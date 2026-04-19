<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        Election::syncComputedStatuses();
        $this->deleteCandidatesForCompletedElections();

        $positions = Position::get()
            ->mapWithKeys(fn ($e) => [$e->id => $e->name]);

        $openElections = Election::query()
            ->get()
            ->filter(fn ($e) => in_array((string) $e->status, ['pending', 'current'], true));

        $lockedUpcomingElection = Election::resolveUpcomingCountdownElection();
        $ballotCountdownDaysMax = Election::BALLOT_COUNTDOWN_DAYS_MAX;

        $elections = $lockedUpcomingElection
            ? collect([$lockedUpcomingElection->id => $lockedUpcomingElection->title])
            : $openElections->mapWithKeys(fn ($e) => [$e->id => $e->title]);

        $organizations = Organization::query()
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn ($o) => [$o->id => $o->name]);

        $q = trim($request->get('q', ''));
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        // Candidate names plus related org/position/election titles are encrypted at rest.
        if ($q === '') {
            $candidates = Candidate::query()
                ->with(['position', 'organization', 'election'])
                ->latest()
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $filtered = Candidate::query()
                ->with(['position', 'organization', 'election'])
                ->latest()
                ->get()
                ->filter(function (Candidate $candidate) use ($q) {
                    if (mb_stripos((string) $candidate->first_name, $q) !== false) {
                        return true;
                    }
                    if (mb_stripos((string) $candidate->last_name, $q) !== false) {
                        return true;
                    }
                    if (mb_stripos(trim((string) $candidate->first_name . ' ' . (string) $candidate->last_name), $q) !== false) {
                        return true;
                    }
                    if (mb_stripos((string) ($candidate->organization?->name ?? ''), $q) !== false) {
                        return true;
                    }
                    if (mb_stripos((string) ($candidate->position?->name ?? ''), $q) !== false) {
                        return true;
                    }
                    if (mb_stripos((string) ($candidate->election?->title ?? ''), $q) !== false) {
                        return true;
                    }

                    return false;
                })
                ->values();

            $page = max(1, (int) $request->get('page', 1));
            $candidates = new LengthAwarePaginator(
                $filtered->slice(($page - 1) * $perPage, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('admin.candidates.index', compact(
            'candidates',
            'q',
            'perPage',
            'positions',
            'elections',
            'organizations',
            'lockedUpcomingElection',
            'ballotCountdownDaysMax',
        ));
    }

    public function store(Request $request)
    {
        Election::syncComputedStatuses();

        $allowedElectionIds = $this->allowedElectionIdsForRequest(null);
        $data = $request->validate([
            'election' => ['required', Rule::in($allowedElectionIds)],
            'position' => 'required',
            'organization' => 'required|exists:organizations,id',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            // 'admin_id'       			=> 'required|string',
            // 'password'       			=> 'required|string',
        ]);

        // assert_current_user_is_admin();
        // assert_admin_credentials($data['admin_id'], $data['password']);

        Candidate::create([
            'election_id' => $data['election'],
            'position_id' => $data['position'],
            'organization_id' => $data['organization'],
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
        ]);

        return redirect()->route('admin.candidates.index')
            ->with([
                'success' => 'Successfully Submitted',
                'buttonText' => 'Proceed',
            ]);
    }

    public function update(Request $request, Candidate $candidate)
    {
        Election::syncComputedStatuses();

        $allowedElectionIds = $this->allowedElectionIdsForRequest($candidate);
        $data = $request->validate([
            'election' => ['required', Rule::in($allowedElectionIds)],
            'position' => 'required',
            'organization' => 'required|exists:organizations,id',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            // 'admin_id'       			=> 'required|string',
            // 'password'       			=> 'required|string',
        ]);

        // assert_current_user_is_admin();
        // assert_admin_credentials($data['admin_id'], $data['password']);

        $candidate->update([
            'election_id' => $data['election'],
            'position_id' => $data['position'],
            'organization_id' => $data['organization'],
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
        ]);

        return redirect()->route('admin.candidates.index')
            ->with([
                'success' => 'Successfully Submitted',
                'buttonText' => 'Proceed',
            ]);
    }

    public function destroy(Candidate $candidate)
    {
        $candidate->delete();

        return redirect()->route('admin.candidates.index')
            ->with([
                'success' => 'Candidate deleted.',
                'buttonText' => 'Proceed',
            ]);
    }

    /**
     * Candidates tied to elections whose status is completed (ended) are removed automatically.
     * Vote rows cascade on candidate delete.
     */
    private function deleteCandidatesForCompletedElections(): void
    {
        $completedElectionIds = Election::query()
            ->get()
            ->filter(fn ($e) => (string) $e->status === 'completed')
            ->pluck('id');

        if ($completedElectionIds->isEmpty()) {
            return;
        }

        Candidate::query()
            ->whereIn('election_id', $completedElectionIds)
            ->delete();
    }

    /**
     * @return list<int|string>
     */
    private function allowedElectionIdsForRequest(?Candidate $candidate): array
    {
        $open = Election::query()
            ->get()
            ->filter(fn ($e) => in_array((string) $e->status, ['pending', 'current'], true));

        $lockedUpcoming = Election::resolveUpcomingCountdownElection();

        if ($lockedUpcoming) {
            if ($candidate === null) {
                return [(string) $lockedUpcoming->id];
            }

            return (string) $candidate->election_id === (string) $lockedUpcoming->id
                ? [(string) $lockedUpcoming->id]
                : [(string) $candidate->election_id];
        }

        return $open->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
    }
}
