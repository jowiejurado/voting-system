<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;

class ElectionController extends Controller
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
							->whereDate('date', '>', $now->toDateString())
							// OR Current: same date and end_time still ahead
							->orWhere(function ($q2) use ($now) {
									$q2->whereDate('date', $now->toDateString())
										->whereTime('end_time', '>=', $now->toTimeString());
							});
			})
			->orderBy('date', 'asc')       // soonest first; adjust if you prefer latest()
			->orderBy('end_time', 'asc')
			->paginate($perPage)
			->withQueryString();

		return view('admin.elections.index', compact('elections', 'q', 'perPage'));
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'title'       => 'required|string|max:255',
			'date'  			=> 'required',
			'start_time'  => 'required',
			'end_time'    => 'required',
			'admin_id'    => 'required|string',
			'password'    => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		Election::create([
			'title'       => $data['title'],
			'date'  			=> $data['date'],
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
			'date'  			=> 'required',
			'start_time'  => 'required',
			'end_time'    => 'required',
			'admin_id'    => 'required|string',
			'password'    => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		$election->update([
			'title'       => $data['title'],
			'date'  			=> $data['date'],
			'start_time'  => $data['start_time'],
			'end_time'  	=> $data['end_time'],
		]);

		return redirect()->route('admin.elections.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function destroy(Request $request, Election $election)
	{
		$request->validate([
			'admin_id' => 'required|string',
			'password' => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($request->input('admin_id'), $request->input('password'));

		$election->delete();

		return back()->with([
			'success' => 'Removed Successfully',
			'buttonText' => 'Proceed'
		]);
	}
}
