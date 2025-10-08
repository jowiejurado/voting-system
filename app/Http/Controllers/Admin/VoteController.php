<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VoteController extends Controller
{
	public function index(Request $request)
	{
		$q 				= trim($request->get('q', ''));
		$perPage 	= (int) $request->get('per_page', 10);
		$perPage 	= $perPage > 0 && $perPage <= 100 ? $perPage : 10;

		$votes = \App\Models\Vote::query()
			->with([
				'position',
				'candidate',
				'election'
			])
			->latest()
			->paginate($perPage)
			->withQueryString();

		return view('admin.votes.index', compact('votes', 'q', 'perPage'));
	}

	public function voterStatus(Request $request)
	{
		$q       = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 9);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 9;

		$sort = $request->get('sort', '');
		$orderForHasVoted = $sort === 'voted' ? 'desc' : 'asc';

		$voters = \App\Models\User::query()
			->where('type', 'voter')
			->when($q !== '', function ($query) use ($q) {
				$query->where(function ($sub) use ($q) {
					$sub->where('first_name', 'like', "%{$q}%")
						->orWhere('last_name', 'like', "%{$q}%")
						->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$q}%"])
						->orWhere('organization_name', 'like', "%{$q}%")
						->orWhere('email', 'like', "%{$q}%");
				});
			})
			->orderBy('has_voted', $orderForHasVoted)
			->orderByDesc('created_at')
			->simplePaginate($perPage)
			->withQueryString();

		return view('admin.voter-status.index', compact('voters', 'q', 'perPage', 'sort'));
	}
}
