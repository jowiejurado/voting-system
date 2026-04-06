<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitBallotRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BallotController extends Controller
{
    public function showBallot(Request $request)
    {
        $now = Election::nowForSchedule();

        $election = Election::findActiveForBallot($now);

        if ($election) {
            $user = Auth::user();
            $alreadyVoted = Vote::where('election_id', $election->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyVoted) {
                return view('voter.ballot_already_voted', compact('election'));
            }

            $hasCandidates = Candidate::where('election_id', $election->id)->exists();
            if (! $hasCandidates) {
                return view('voter.no_candidates');
            }

            $positions = Position::query()
                ->whereHas('candidates', function ($q) use ($election) {
                    $q->where('election_id', $election->id);
                })
                ->with(['candidates' => function ($q) use ($election) {
                    $q->where('election_id', $election->id)
                        ->with('organization')
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])
                ->orderBy('id')
                ->get();

            $positionsPayload = $positions->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'max' => $p->maximum_votes,
                    'candidates' => $p->candidates->map(function ($c) {
                        return [
                            'id' => $c->id,
                            'name' => trim($c->last_name.', '.$c->first_name),
                            'org' => $c->organization?->name ?? '',
                        ];
                    })->values()->all(),
                ];
            })->values()->all();

            $endAt = Election::parseScheduleEnd($election);
            $votingEndsAtTimestampMs = $endAt ? $endAt->getTimestampMs() : null;

            return view('voter.ballot', compact('election', 'positions', 'positionsPayload', 'votingEndsAtTimestampMs'));
        }

        $next = Election::resolveBallotCountdownContext($now);

        if (! $next) {
            return view('voter.ballot_closed');
        }

        $startAt = $next['startAt'];
        $endAt = $next['endAt'];
        $nextElection = $next['election'];

        $startTimestampMs = $startAt->getTimestampMs();
        $endTimestampMs = $endAt->getTimestampMs();
        $endAtFormatted = $endAt->format('F j, Y g:i A');

        return view('voter.ballot_countdown', [
            'election' => $nextElection,
            'startTimestampMs' => $startTimestampMs,
            'endTimestampMs' => $endTimestampMs,
            'endAtFormatted' => $endAtFormatted,
        ]);
    }

    public function submit(SubmitBallotRequest $request)
    {
        $user = Auth::user();

        $election = Election::findOrFail($request->input('election_id'));

        // Re-check election window: strict (start inclusive, end exclusive)
        $now = Election::nowForSchedule();
        $startAt = Election::parseScheduleStart($election);
        $endAt = Election::parseScheduleEnd($election);

        if (! $startAt || ! $endAt || $now->lt($startAt) || $now->gte($endAt)) {
            return back()->withErrors(['election' => 'This election is not currently accepting votes.'])->withInput();
        }

        // Optional: block double-voting (one-shot policy)
        $alreadyVoted = Vote::where('election_id', $election->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyVoted) {
            return redirect()->route('voter.ballot')->withErrors(['vote' => 'You have already cast your vote for this election.']);
        }

        $positionsPayload = $request->input('positions', []); // [position_id => [candidate_id, ...]]

        DB::transaction(function () use ($positionsPayload, $election, $user) {
            foreach ($positionsPayload as $positionId => $candidateIds) {
                $candidateIds = array_filter((array) $candidateIds); // allow skip => empty
                foreach ($candidateIds as $cid) {
                    Vote::create([
                        'election_id' => $election->id,
                        'position_id' => $positionId,
                        'candidate_id' => $cid,
                        'user_id' => $user->id,
                    ]);
                }
            }
        });

        $userModel = User::find($user->id);
        $userModel->forceFill(['has_voted' => true])->save();

        return redirect()
            ->route('voter.ballot')
            ->with(['success' => 'Your vote has been submitted. Thank you!']);
    }
}
