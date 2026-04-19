<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ArchiveElectionController extends Controller
{
	public function index(Request $request)
	{
		$q         = trim($request->query('q', ''));
		$perPage   = (int) $request->query('per_page', 10);
		$perPage   = $perPage > 0 && $perPage <= 100 ? $perPage : 10;
		$now = now('Asia/Manila');

		// New filters
		$startDate = $request->query('start_date');
		$endDate   = $request->query('end_date');
		$startTime = $request->query('start_time');
		$endTime   = $request->query('end_time');
		$createdAt = $request->query('created_at');
		$month     = $request->query('month');
		$year      = $request->query('year');

		$hasFilters = (string) $startDate !== '' || (string) $endDate !== '' ||
									(string) $startTime !== '' || (string) $endTime !== '' ||
									(string) $createdAt !== '' || (string) $month !== '' ||
									(string) $year !== '';

		if (! $hasFilters) {
			// In-memory filtering (columns may be encrypted): only show elections that have ended (end_date + end_time < now)
			$all = Election::query()->get();

			$filtered = $all->filter(function ($item) use ($q, $now) {
				try {
					$ed = $item->end_date
						? \Illuminate\Support\Carbon::parse((string) $item->end_date, 'Asia/Manila')->toDateString()
						: \Illuminate\Support\Carbon::parse((string) $item->start_date, 'Asia/Manila')->toDateString();
					$endAt = \Illuminate\Support\Carbon::parse($ed . ' ' . (string) $item->end_time, 'Asia/Manila');
					$hasEnded = $now->gt($endAt);

					if (! $hasEnded) {
						return false;
					}
					if ($q !== '' && mb_stripos((string) $item->title, $q) === false) {
						return false;
					}
					return true;
				} catch (\Throwable $e) {
					return false;
				}
			});

			$sorted = $filtered->sortBy(function ($item) {
				try {
					$sd = \Illuminate\Support\Carbon::parse((string) $item->start_date, 'Asia/Manila')->toDateString();
					$ed = $item->end_date
						? \Illuminate\Support\Carbon::parse((string) $item->end_date, 'Asia/Manila')->toDateString()
						: $sd;
					return \Illuminate\Support\Carbon::parse($ed . ' ' . (string) $item->end_time, 'Asia/Manila')->timestamp;
				} catch (\Throwable $e) {
					return PHP_INT_MAX;
				}
			})->values();

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

			return view('admin.archives.index', compact('elections', 'q', 'perPage'));
		}

		// With filters -> in-memory filtering due to encrypted columns
		$all = Election::query()->get();

		$filtered = $all->filter(function ($item) use ($q, $now, $startDate, $endDate, $startTime, $endTime, $createdAt, $month, $year) {
			try {
				$title = (string) $item->title;
				$sd = \Illuminate\Support\Carbon::parse((string) $item->start_date, 'Asia/Manila')->toDateString();
				$ed = $item->end_date
					? \Illuminate\Support\Carbon::parse((string) $item->end_date, 'Asia/Manila')->toDateString()
					: $sd;
				$st = \Illuminate\Support\Carbon::parse((string) $item->start_time, 'Asia/Manila')->format('H:i');
				$et = \Illuminate\Support\Carbon::parse((string) $item->end_time, 'Asia/Manila')->format('H:i');

				// archived if end < today or end is today and time passed
				$today = $now->toDateString();
				$isArchived = $ed < $today || ($ed === $today && $now->toTimeString() >= $et);
				if (! $isArchived) return false;

				// search
				if ($q !== '' && mb_stripos($title, $q) === false) return false;

				// start/end date
				if (!empty($startDate) && $sd !== \Illuminate\Support\Carbon::parse($startDate, 'Asia/Manila')->toDateString()) return false;
				if (!empty($endDate) && $ed !== \Illuminate\Support\Carbon::parse($endDate, 'Asia/Manila')->toDateString()) return false;

				// times (allow independent filters)
				if (!empty($startTime) && $st !== \Illuminate\Support\Carbon::parse($startTime, 'Asia/Manila')->format('H:i')) return false;
				if (!empty($endTime) && $et !== \Illuminate\Support\Carbon::parse($endTime, 'Asia/Manila')->format('H:i')) return false;
				// if both times provided, also ensure range containment
				if (!empty($startTime) && !empty($endTime)) {
					if (!($st >= $startTime && $et <= $endTime)) return false;
				}

				// month / year from start date
				if (!empty($month)) {
					$m = (int) \Illuminate\Support\Carbon::parse($sd, 'Asia/Manila')->month;
					if ($m !== (int) $month) return false;
				}
				if (!empty($year)) {
					$y = (int) \Illuminate\Support\Carbon::parse($sd, 'Asia/Manila')->year;
					if ($y !== (int) $year) return false;
				}

				// created_at (match by date)
				if (!empty($createdAt)) {
					$needleDate = \Illuminate\Support\Carbon::parse($createdAt, 'Asia/Manila')->toDateString();
					$itemCreated = $item->created_at ? $item->created_at->copy()->timezone('Asia/Manila')->toDateString() : null;
					if ($itemCreated !== $needleDate) return false;
				}

				return true;
			} catch (\Throwable $e) {
				return false;
			}
		});

		// Sort by end timestamp asc
		$sorted = $filtered->sortBy(function ($item) {
			try {
				$sd = \Illuminate\Support\Carbon::parse((string) $item->start_date, 'Asia/Manila')->toDateString();
				$ed = $item->end_date
					? \Illuminate\Support\Carbon::parse((string) $item->end_date, 'Asia/Manila')->toDateString()
					: $sd;
				return \Illuminate\Support\Carbon::parse($ed . ' ' . (string) $item->end_time, 'Asia/Manila')->timestamp;
			} catch (\Throwable $e) {
				return PHP_INT_MAX;
			}
		})->values();

		// Manual pagination
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
