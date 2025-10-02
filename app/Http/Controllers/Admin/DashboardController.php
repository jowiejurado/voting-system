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
		$stats = [
			'candidates' => Candidate::count(),
			'positions'  => Position::count(),
			'voters'     => User::where('type', 'voter')->count(),
			'voted'      => Vote::distinct('user_id')->count('user_id'),
		];

		$charts = [];
		$active = Election::where('is_active', true)->first();

		if ($active) {
			$positions = Position::with(['candidates:id,first_name,last_name,position_id'])->get();

			$votesByCandidate = Vote::select('candidate_id', DB::raw('COUNT(*) as votes'))
				->where('election_id', $active->id)
				->groupBy('candidate_id')
				->pluck('votes', 'candidate_id');

			foreach ($positions as $position) {
				$labels = [];
				$data   = [];

				foreach ($position->candidates as $candidate) {
					$labels[] = $candidate->first_name . ' ' . $candidate->last_name;
					$data[]   = (int) ($votesByCandidate[$candidate->id] ?? 0);
				}

				$charts[] = [
					'position' => $position->name,
					'labels'   => $labels,
					'data'     => $data,
				];
			}
		}

		return view('admin.dashboard', compact('stats', 'charts'));
	}

	public function toggleElection(Request $request)
	{
		$data = $request->validate([
			'title' => 'required|string',
			'active' => 'required|boolean',
			'starts_at' => 'nullable|date',
			'ends_at' => 'nullable|date|after_or_equal:starts_at',
		]);
		$election = Election::firstOrCreate(['title' => $data['title']]);
		$election->is_active = (bool) $data['active'];
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
