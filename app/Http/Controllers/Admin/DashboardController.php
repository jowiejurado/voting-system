<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
	public function index()
	{
		Election::syncComputedStatuses();

		$now = Election::nowForSchedule();

		// Tallies and vote stats only while the ballot window is open (same as BallotController).
		$currentElection = Election::findActiveForBallot($now);

		// Positions card: total configured positions (Manage → Positions). Candidates,
		// voted, and charts stay scoped to the resolved election.
		$stats = [
			'positions'  => Position::query()->count(),
			'candidates' => 0,
			'voters'     => User::where('type', 'voter')->count(),
			'voted'      => 0,
		];

		$charts = [];

		if ($currentElection) {
			// Positions that have candidates in THIS election
			$positions = Position::whereHas('candidates', function ($q) use ($currentElection) {
				$q->where('election_id', $currentElection->id);
			})
				->with(['candidates' => function ($q) use ($currentElection) {
					$q->where('election_id', $currentElection->id);
				}])
				->get();

			$stats['candidates'] = Candidate::where('election_id', $currentElection->id)
				->count();

			$stats['voted'] = (int) Vote::query()
				->where('election_id', $currentElection->id)
				->selectRaw('count(distinct user_id) as aggregate')
				->value('aggregate');

			// Votes per candidate for this election
			$votesByCandidate = Vote::select('candidate_id', DB::raw('COUNT(*) as votes'))
				->where('election_id', $currentElection->id)
				->groupBy('candidate_id')
				->pluck('votes', 'candidate_id');

			foreach ($positions as $position) {
				$labels = [];
				$data   = [];

				foreach ($position->candidates as $candidate) {
					$labels[] = $candidate->first_name . ' ' . $candidate->last_name;
					$data[]   = (int) ($votesByCandidate[$candidate->id] ?? 0);
				}

				if (!empty($labels)) {
					$charts[] = [
						'position' => $position->name,
						'labels'   => $labels,
						'data'     => $data,
					];
				}
			}
		}

		return view('admin.dashboard', [
			'stats'           => $stats,
			'charts'          => $charts,
			'currentElection' => $currentElection,
		]);
	}

	public function toggleElection(Request $request)
	{
		$data = $request->validate([
			'title' => 'required|string',
			'status' => 'required',
			'starts_at' => 'nullable|date',
			'ends_at' => 'nullable|date|after_or_equal:starts_at',
		]);
		$election = Election::firstOrCreate(['title' => $data['title']]);
		$election->status = $data['status'];
		if (!empty($data['starts_at'])) {
			$election->starts_at = $data['starts_at'];
		} elseif (!$election->starts_at) {
			$election->starts_at = now();
		}
		if (!empty($data['ends_at'])) {
			$election->ends_at = $data['ends_at'];
		}
		$election->save();
		return back()->with('success', 'Election updated');
	}
}
