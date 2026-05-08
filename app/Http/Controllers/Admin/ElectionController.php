<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ElectionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;
        Election::syncComputedStatuses();

        // Fetch all to filter/sort in-memory because searchable columns are encrypted at rest
        $all = Election::query()->get();

        // Apply search and exclude completed
        $filtered = $all->filter(function ($item) use ($q) {
            if ($q !== '') {
                $title = (string) $item->title;
                if (mb_stripos($title, $q) === false) {
                    return false;
                }
            }

            return (string) $item->status !== 'completed';
        });

        // Sort by end timestamp asc using Carbon parsing
        $sorted = $filtered->sortBy(function ($item) {
            try {
                $startDate = \Illuminate\Support\Carbon::parse((string) $item->start_date, 'Asia/Manila')->toDateString();
                $endDate = $item->end_date
                    ? \Illuminate\Support\Carbon::parse((string) $item->end_date, 'Asia/Manila')->toDateString()
                    : $startDate;
                $et = \Illuminate\Support\Carbon::parse($endDate.' '.(string) $item->end_time, 'Asia/Manila')->timestamp;

                return $et;
            } catch (\Throwable $e) {
                return PHP_INT_MAX;
            }
        })->values();

        // Manual pagination on the collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $sorted->forPage($currentPage, $perPage)->values();
        $elections = new LengthAwarePaginator(
            $currentItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        $elections->appends($request->query());

        return view('admin.elections.index', compact('elections', 'q', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedElectionPayload($request, null);

        // assert_current_user_is_admin();
        // assert_admin_credentials($data['admin_id'], $data['password']);

        $election = Election::create([
            'title' => $data['title'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ]);

        $startAt = Election::parseScheduleStart($election);
        if ($startAt && Election::nowForSchedule()->lt($startAt)) {
            User::query()->where('type', 'voter')->update(['has_voted' => false]);
        }

        return redirect()->route('admin.elections.index')
            ->with([
                'success' => 'Successfully Submitted',
                'buttonText' => 'Proceed',
            ]);
    }

    public function update(Request $request, Election $election)
    {
        $data = $this->validatedElectionPayload($request, $election->id);

        // assert_current_user_is_admin();
        // assert_admin_credentials($data['admin_id'], $data['password']);

        $election->update([
            'title' => $data['title'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ]);

        return redirect()->route('admin.elections.index')
            ->with([
                'success' => 'Successfully Submitted',
                'buttonText' => 'Proceed',
            ]);
    }

    public function destroy(Election $election)
    {
        $election->delete();

        return redirect()->route('admin.elections.index')
            ->with([
                'success' => 'Election deleted.',
                'buttonText' => 'Proceed',
            ]);
    }

    /**
     * @return array{title: string, start_date: string, end_date: string, start_time: string, end_time: string}
     */
    private function validatedElectionPayload(Request $request, ?int $exceptElectionId): array
    {
        return Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
        ])->after(function ($validator) use ($exceptElectionId) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $d = $validator->getData();

            $window = new Election([
                'start_date' => $d['start_date'],
                'end_date' => $d['end_date'],
                'start_time' => $d['start_time'],
                'end_time' => $d['end_time'],
            ]);

            $startAt = Election::parseScheduleStart($window);
            $endAt = Election::parseScheduleEnd($window);

            if (! $startAt || ! $endAt || $endAt->lte($startAt)) {
                $validator->errors()->add(
                    'end_time',
                    __('The voting end must be after the start.'),
                );

                return;
            }

            if ($this->electionTitleIsDuplicate((string) $d['title'], $exceptElectionId)) {
                $validator->errors()->add(
                    'election_duplicate',
                    __('An election with this title already exists for an upcoming or in-progress election.'),
                );

                return;
            }

            if ($this->electionScheduleOverlaps($startAt, $endAt, $exceptElectionId)) {
                $validator->errors()->add(
                    'election_duplicate',
                    __('This schedule matches or overlaps another election that is still upcoming or in progress. Use a non-overlapping window.'),
                );
            }
        })->validate();
    }

    /**
     * Encrypted title is not suitable for DB-level unique checks; compare decrypted values.
     */
    private function electionTitleIsDuplicate(string $title, ?int $ignoreElectionId): bool
    {
        $norm = mb_strtolower(trim($title));

        return Election::query()
            ->get()
            ->filter(function (Election $e) use ($ignoreElectionId) {
                if ((string) $e->status === 'completed') {
                    return false;
                }
                if ($ignoreElectionId !== null && (int) $e->id === $ignoreElectionId) {
                    return false;
                }

                return true;
            })
            ->contains(fn (Election $e) => mb_strtolower(trim((string) $e->title)) === $norm);
    }

    /**
     * Half-open window matches the ballot: active when start <= now < end.
     */
    private function electionScheduleOverlaps(
        Carbon $startAt,
        Carbon $endAt,
        ?int $ignoreElectionId,
    ): bool {
        foreach (Election::query()->get() as $existing) {
            if ((string) $existing->status === 'completed') {
                continue;
            }
            if ($ignoreElectionId !== null && (int) $existing->id === $ignoreElectionId) {
                continue;
            }

            $exStart = Election::parseScheduleStart($existing);
            $exEnd = Election::parseScheduleEnd($existing);
            if (! $exStart || ! $exEnd || $exEnd->lte($exStart)) {
                continue;
            }

            if ($startAt->lt($exEnd) && $exStart->lt($endAt)) {
                return true;
            }
        }

        return false;
    }
}
