<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ElectionController extends Controller
{
	public function index(Request $request)
	{
		$q = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 10);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;
		$now = now('Asia/Manila');

		// Update statuses without relying on DB-level comparisons (columns are encrypted)
		$toCheck = Election::query()->get();

		foreach ($toCheck as $item) {
			$newStatus = $item->status;
			$today = $now->toDateString();
			$startDate = \Illuminate\Support\Carbon::parse((string) $item->start_date, 'Asia/Manila')->toDateString();
			$endDate = $item->end_date
				? \Illuminate\Support\Carbon::parse((string) $item->end_date, 'Asia/Manila')->toDateString()
				: $startDate;

			if ($endDate < $today) {
				// Past date -> completed
				$newStatus = 'completed';
			} elseif ($startDate > $today) {
				// Future date -> pending
				$newStatus = 'pending';
			} else {
				// Within date range (start <= today <= end) -> use full start/end datetime
				try {
					$startAt = \Illuminate\Support\Carbon::parse($startDate . ' ' . (string) $item->start_time, 'Asia/Manila');
					$endAt = \Illuminate\Support\Carbon::parse($endDate . ' ' . (string) $item->end_time, 'Asia/Manila');

					if ($now->lt($startAt)) {
						$newStatus = 'pending';
					} elseif ($now->gt($endAt)) {
						$newStatus = 'completed';
					} else {
						$newStatus = 'current'; // in progress until end_date + end_time
					}
				} catch (\Throwable $e) {
					// If parsing fails, default to completed to archive invalidly timed elections
					$newStatus = 'completed';
				}
			}

			if ($newStatus !== $item->status) {
				$item->status = $newStatus;
				$item->save();
			}
		}

		// Fetch all to filter/sort in-memory because searchable columns are encrypted at rest
		$all = Election::query()->get();

		// Apply search and exclude completed
		$filtered = $all->filter(function ($item) use ($q) {
			if ($q !== '') {
				$title = (string) $item->title;
				if (stripos($title, $q) === false) {
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
				$et = \Illuminate\Support\Carbon::parse($endDate . ' ' . (string) $item->end_time, 'Asia/Manila')->timestamp;
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
		$data = $request->validate([
			'title'       => 'required|string|max:255',
			'start_date'  => 'required|date',
			'end_date'    => 'required|date|after_or_equal:date',
			'start_time'  => 'required|date_format:H:i:s',
			'end_time'    => 'required|date_format:H:i:s',
			// 'admin_id'    => 'required|string',
			// 'password'    => 'required|string',
		]);

		// assert_current_user_is_admin();
    // assert_admin_credentials($data['admin_id'], $data['password']);

		Election::create([
			'title'       => $data['title'],
			'start_date'  => $data['start_date'],
			'end_date'    => $data['end_date'],
			'start_time'  => $data['start_time'],
			'end_time'  	=> $data['end_time'],
		]);

		return redirect()->route('admin.elections.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function update(Request $request, Election $election)
	{
		$data = $request->validate([
			'title'       => 'required|string|max:255',
			'start_date'  => 'required|date',
			'end_date'    => 'required|date|after_or_equal:date',
			'start_time'  => 'required|date_format:H:i:s',
			'end_time'    => 'required|date_format:H:i:s',
			// 'admin_id'    => 'required|string',
			// 'password'    => 'required|string',
		]);

		// assert_current_user_is_admin();
    // assert_admin_credentials($data['admin_id'], $data['password']);

		$election->update([
			'title'       => $data['title'],
			'start_date'  => $data['start_date'],
			'end_date'    => $data['end_date'],
			'start_time'  => $data['start_time'],
			'end_time'  	=> $data['end_time'],
		]);

		return redirect()->route('admin.elections.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}
}
