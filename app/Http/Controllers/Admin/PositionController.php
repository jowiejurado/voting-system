<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PositionController extends Controller
{
	public function index(Request $request)
	{
		$q = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 10);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

		if ($q === '') {
			$positions = Position::query()
				->latest()
				->paginate($perPage)
				->withQueryString();
		} else {
			$votesMatch = is_numeric($q) ? (int) $q : null;

			$filtered = Position::query()
				->latest()
				->get()
				->filter(function (Position $position) use ($q, $votesMatch) {
					if (mb_stripos((string) $position->name, $q) !== false) {
						return true;
					}
					if ($votesMatch !== null && (int) $position->maximum_votes === $votesMatch) {
						return true;
					}

					return false;
				})
				->values();

			$page = max(1, (int) $request->get('page', 1));
			$total = $filtered->count();
			$slice = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

			$positions = new LengthAwarePaginator(
				$slice,
				$total,
				$perPage,
				$page,
				['path' => $request->url(), 'query' => $request->query()]
			);
		}

		return view('admin.positions.index', compact('positions', 'q', 'perPage'));
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'name'           => 'required|string|max:255',
			'maximum_votes'  => 'required|integer|min:1',
			// 'admin_id'       => 'required|string',
			// 'password'       => 'required|string',
		]);

		// assert_current_user_is_admin();
    // assert_admin_credentials($data['admin_id'], $data['password']);

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
			// 'admin_id'       => 'required|string',
			// 'password'       => 'required|string',
		]);

		// assert_current_user_is_admin();
    // assert_admin_credentials($data['admin_id'], $data['password']);

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

	public function destroy(Position $position)
	{
		$position->delete();

		return redirect()->route('admin.positions.index')
			->with([
				'success' => 'Position deleted.',
				'buttonText' => 'Proceed',
			]);
	}
}
