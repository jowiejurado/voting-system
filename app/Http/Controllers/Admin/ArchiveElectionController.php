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

class ArchiveElectionController extends Controller
{
	public function index(Request $request)
	{
		$q = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 10);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;
		$now = now('Asia/Manila');

		$elections = \App\Models\Election::query()
			->when($q !== '', function ($query) use ($q) {
				$query->where(function ($sub) use ($q) {
					$sub->where('title', 'like', "%{$q}%");
				});
			})
			->where(function ($query) use ($now) {
				$query
					// Upcoming: any date after today
					->whereDate('date', '<', $now->toDateString())
					// OR Current: same date and end_time still ahead
					->orWhere(function ($q2) use ($now) {
						$q2->whereDate('date', $now->toDateString())
							->whereTime('end_time', '<=', $now->toTimeString());
					});
			})
			->orderBy('date', 'asc')       // soonest first; adjust if you prefer latest()
			->orderBy('end_time', 'asc')
			->paginate($perPage)
			->withQueryString();

		return view('admin.archives.index', compact('elections', 'q', 'perPage'));
	}

	/**
	 * Show the result of a single archived election.
	 */
	public function show(Election $election)
	{
		// Global voters (optional)
		$votersCount = User::where('type', 'voter')->count();

		// Positions that have candidates in THIS election
		$positions = Position::whereHas('candidates', function ($q) use ($election) {
			$q->where('election_id', $election->id);
		})
			->with(['candidates' => function ($q) use ($election) {
				$q->where('election_id', $election->id);
			}])
			->get();

		// Stats for this election
		$stats = [
			'positions'  => $positions->count(),
			'candidates' => Candidate::where('election_id', $election->id)->count(),
			'voters'     => $votersCount,
			'voted'      => Vote::where('election_id', $election->id)
				->distinct('user_id')
				->count('user_id'),
		];

		// Votes per candidate for this election
		$votesByCandidate = Vote::select('candidate_id', DB::raw('COUNT(*) as votes'))
			->where('election_id', $election->id)
			->groupBy('candidate_id')
			->pluck('votes', 'candidate_id');

		// Build sorted results per position for the view
		$positionResults = [];

		foreach ($positions as $position) {
			$rows = $position->candidates
				->map(function ($candidate) use ($votesByCandidate) {
					return [
						'name'  => $candidate->first_name . ' ' . $candidate->last_name,
						'votes' => (int) ($votesByCandidate[$candidate->id] ?? 0),
					];
				})
				->sortByDesc('votes')
				->values()
				->toArray();

			$positionResults[] = [
				'position' => $position->name,
				'rows'     => $rows,
			];
		}

		return view('admin.archives.show', [
			'election'        => $election,
			'stats'           => $stats,
			'positionResults' => $positionResults,
		]);
	}
}
