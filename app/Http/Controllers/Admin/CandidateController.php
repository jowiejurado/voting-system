<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        Election::syncComputedStatuses();

        // Keep candidates (and votes) for ended elections so archives and tallies stay correct.
        $completedElectionIds = Election::query()
            ->get()
            ->filter(fn ($e) => (string) $e->status === 'completed')
            ->pluck('id');

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
                ->when($completedElectionIds->isNotEmpty(), fn ($query) => $query->whereNotIn('election_id', $completedElectionIds))
                ->with(['position', 'organization', 'election'])
                ->latest()
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $filtered = Candidate::query()
                ->when($completedElectionIds->isNotEmpty(), fn ($query) => $query->whereNotIn('election_id', $completedElectionIds))
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

        $sessionErrors = $request->session()->get('errors');
        $candidateModalErrorKeys = [];
        if ($sessionErrors instanceof ViewErrorBag) {
            $candidateModalErrorKeys = array_diff(
                array_keys($sessionErrors->getMessages()),
                ['upcoming_election']
            );
        }
        $openCandidateModalAfterError = count($candidateModalErrorKeys) > 0;
        $openUnavailableModalAfterError = $sessionErrors instanceof ViewErrorBag
            && $sessionErrors->has('upcoming_election')
            && ! $openCandidateModalAfterError;

        return view('admin.candidates.index', compact(
            'candidates',
            'q',
            'perPage',
            'positions',
            'elections',
            'organizations',
            'lockedUpcomingElection',
            'ballotCountdownDaysMax',
            'openCandidateModalAfterError',
            'openUnavailableModalAfterError',
        ));
    }

    public function store(Request $request)
    {
        Election::syncComputedStatuses();

        if (Election::resolveUpcomingCountdownElection() === null) {
            throw ValidationException::withMessages([
                'upcoming_election' => __('You can add candidates once an election is within 10 days. Check back when the next election is closer.', [
                    'days' => Election::BALLOT_COUNTDOWN_DAYS_MAX,
                ]),
            ]);
        }

        $allowedElectionIds = $this->allowedElectionIdsForRequest(null);

        $data = Validator::make($request->all(), [
            'election' => ['required', Rule::in($allowedElectionIds)],
            'position' => ['required', 'exists:positions,id'],
            'organization' => ['required', 'exists:organizations,id'],
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
        ])->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $d = $validator->getData();
            if ($this->candidateIsDuplicate(
                $d['election'],
                $d['position'],
                $d['organization'],
                (string) $d['first_name'],
                (string) $d['last_name'],
                null,
            )) {
                $validator->errors()->add(
                    'candidate_duplicate',
                    __('A candidate with this name is already registered for this election, position, and organization.'),
                );
            }
        })->validate();

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

        $data = Validator::make($request->all(), [
            'election' => ['required', Rule::in($allowedElectionIds)],
            'position' => ['required', 'exists:positions,id'],
            'organization' => ['required', 'exists:organizations,id'],
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
        ])->after(function ($validator) use ($candidate) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $d = $validator->getData();
            if ($this->candidateIsDuplicate(
                $d['election'],
                $d['position'],
                $d['organization'],
                (string) $d['first_name'],
                (string) $d['last_name'],
                $candidate->id,
            )) {
                $validator->errors()->add(
                    'candidate_duplicate',
                    __('A candidate with this name is already registered for this election, position, and organization.'),
                );
            }
        })->validate();

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

        if ($candidate === null) {
            return [];
        }

        return $open->pluck('id')->map(fn ($id) => (string) $id)->values()->all();
    }

    /**
     * Encrypted name columns are not suitable for DB-level unique checks; compare decrypted values.
     *
     * @param  int|string  $electionId
     * @param  int|string  $positionId
     * @param  int|string  $organizationId
     */
    private function candidateIsDuplicate(
        $electionId,
        $positionId,
        $organizationId,
        string $firstName,
        string $lastName,
        ?int $ignoreCandidateId,
    ): bool {
        $firstNorm = mb_strtolower(trim($firstName));
        $lastNorm = mb_strtolower(trim($lastName));

        return Candidate::query()
            ->where('election_id', $electionId)
            ->where('position_id', $positionId)
            ->where('organization_id', $organizationId)
            ->when($ignoreCandidateId !== null, fn ($q) => $q->where('id', '!=', $ignoreCandidateId))
            ->get()
            ->contains(function (Candidate $c) use ($firstNorm, $lastNorm) {
                return mb_strtolower(trim((string) $c->first_name)) === $firstNorm
                    && mb_strtolower(trim((string) $c->last_name)) === $lastNorm;
            });
    }
}
