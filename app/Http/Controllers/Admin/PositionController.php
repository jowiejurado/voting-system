<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
	public function index(Request $request)
	{
		$q = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 10);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

		$positions = \App\Models\Position::query()
			->when($q !== '', function ($query) use ($q) {
				$query->where(function ($sub) use ($q) {
					$sub->where('name', 'like', "%{$q}%");
					if (is_numeric($q)) $sub->orWhere('maximum_votes', (int) $q);
				});
			})
			->latest()
			->paginate($perPage)
			->withQueryString();

		return view('admin.positions.index', compact('positions', 'q', 'perPage'));
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'name'           => 'required|string|max:255',
			'maximum_votes'  => 'required|integer|min:1',
			'admin_id'       => 'required|string',
			'password'       => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		Position::create([
			'name'           => $data['name'],
			'maximum_votes'  => $data['maximum_votes'],
		]);

		return redirect()->route('admin.positions.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function update(Request $request, Position $position)
	{
		$data = $request->validate([
			'name'           => 'required|string|max:255',
			'maximum_votes'  => 'required|integer|min:1',
			'admin_id'       => 'required|string',
			'password'       => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		$position->update([
			'name'           => $data['name'],
			'maximum_votes'  => $data['maximum_votes'],
		]);

		return redirect()->route('admin.positions.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function destroy(Request $request, Position $position)
	{
		$request->validate([
			'admin_id' => 'required|string',
			'password' => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($request->input('admin_id'), $request->input('password'));

		$position->delete();

		return back()->with([
			'success' => 'Removed Successfully',
			'buttonText' => 'Proceed'
		]);
	}
}
