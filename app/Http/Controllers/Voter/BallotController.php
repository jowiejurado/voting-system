<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitBallotRequest;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BallotController extends Controller
{
	public function showBallot(Request $request)
	{
		$now = Carbon::now('Asia/Manila');
		$today = $now->toDateString();

		// Find active election window
		$election = Election::query()
			->where('is_active', true)
			->whereDate('date', $today)
			->whereTime('start_time', '<=', $now->format('H:i:s'))
			->whereTime('end_time', '>=', $now->format('H:i:s'))
			->first();

		if (!$election) {
			return view('voter.ballot_closed');
		}

		// One-shot policy (optional)
		$user = Auth::user();
		$alreadyVoted = Vote::where('election_id', $election->id)
			->where('user_id', $user->id)
			->exists();

		if ($alreadyVoted) {
			return view('voter.ballot_already_voted', compact('election'));
		}

		$positions = Position::query()
			->whereHas('candidates', function ($q) use ($election) {
				$q->where('election_id', $election->id);
			})
			->with(['candidates' => function ($q) use ($election) {
				$q->where('election_id', $election->id)
					->orderBy('last_name')
					->orderBy('first_name');
			}])
			->orderBy('id')
			->get();

		$positionsPayload = $positions->map(function ($p) {
			return [
				'id'   => $p->id,
				'name' => $p->name,
				'max'  => $p->maximum_votes,
				'candidates' => $p->candidates->map(function ($c) {
					return [
						'id'   => $c->id,
						'name' => trim($c->last_name . ', ' . $c->first_name),
						'org'  => $c->organization_name,
					];
				})->values()->all(),
			];
		})->values()->all();

		return view('voter.ballot', compact('election', 'positions', 'positionsPayload'));
	}

	public function submit(SubmitBallotRequest $request)
	{
		$user = Auth::user();

		$election = Election::findOrFail($request->input('election_id'));

		// Re-check election is active (server-side gate)
		$now = Carbon::now();
		if (
			!$election->is_active ||
			$election->date !== $now->toDateString() ||
			$now->format('H:i:s') < $election->start_time ||
			$now->format('H:i:s') > $election->end_time
		) {
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
						'election_id'  => $election->id,
						'position_id'  => $positionId,
						'candidate_id' => $cid,
						'user_id'      => $user->id,
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
