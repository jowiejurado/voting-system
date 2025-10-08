<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
	public function index(Request $request)
	{
		$positions = Position::pluck('name', 'id');
		$elections = Election::pluck('title', 'id');
		$q = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 10);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

		$candidates = \App\Models\Candidate::query()
			->when($q !== '', function ($query) use ($q) {
				$query->where(function ($sub) use ($q) {
					$sub->where('last_name', 'like', "%{$q}%");
					$sub->orWhere('first_name', 'like', "%{$q}%");
					$sub->orWhere('organization_name', 'like', "%{$q}%");
				});
			})
			->latest()
			->paginate($perPage)
			->withQueryString();

		return view('admin.candidates.index', compact('candidates', 'q', 'perPage', 'positions', 'elections'));
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'election'						=> 'required',
			'position'						=> 'required',
			'last_name'           => 'required|string|max:255',
			'first_name'  				=> 'required|string|max:255',
			'organization_name'  	=> 'required|string|max:255',
			'admin_id'       			=> 'required|string',
			'password'       			=> 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		Candidate::create([
			'election_id'					=> $data['election'],
			'position_id'					=> $data['position'],
			'last_name'           => $data['last_name'],
			'first_name'  				=> $data['first_name'],
			'organization_name'  	=> $data['organization_name'],
		]);

		return redirect()->route('admin.candidates.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function update(Request $request, Candidate $candidate)
	{
		$data = $request->validate([
			'election'						=> 'required',
			'position'						=> 'required',
			'last_name'           => 'required|string|max:255',
			'first_name'  				=> 'required|string|max:255',
			'organization_name'  	=> 'required|string|max:255',
			'admin_id'       			=> 'required|string',
			'password'       			=> 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		$candidate->update([
			'election_id'					=> $data['election'],
			'position_id'					=> $data['position'],
			'last_name'           => $data['last_name'],
			'first_name'  				=> $data['first_name'],
			'organization_name'  	=> $data['organization_name'],
		]);

		return redirect()->route('admin.candidates.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function destroy(Request $request, Candidate $candidate)
	{
		$request->validate([
			'admin_id' => 'required|string',
			'password' => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($request->input('admin_id'), $request->input('password'));

		$candidate->delete();

		return back()->with([
			'success' => 'Removed Successfully',
			'buttonText' => 'Proceed'
		]);
	}
}
