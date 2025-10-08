<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
