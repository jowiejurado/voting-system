<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VoterController extends Controller
{
	public function index(Request $request)
	{
		$q = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 10);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

		$voters = \App\Models\User::query()
			->where('type', 'voter')
			->when($q !== '', function ($query) use ($q) {
				$query->where(function ($sub) use ($q) {
					$sub->where('first_name', 'like', "%{$q}%");
					$sub->orWhere('last_name', 'like', "%{$q}%");
					$sub->orWhere('member_id', 'like', "%{$q}%");
					$sub->orWhere('organization_name', 'like', "%{$q}%");
				});
			})
			->latest()
			->paginate($perPage)
			->withQueryString();

		return view('admin.voters.index', compact('voters', 'q', 'perPage'));
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'first_name'          => 'required|string|max:255',
			'last_name'           => 'required|string|max:255',
			'organization_name'   => 'required|string|max:255',
			'phone_number'				=> 'required|string',
			'admin_id'       			=> 'required|string',
			'password'       			=> 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		User::create([
			'last_name'          	=> $data['last_name'],
			'first_name' 				 	=> $data['first_name'],
			'phone_number'				=> $data['phone_number'],
			'member_id'					 	=> generate_admin_id(),
			'organization_name'		=> $data['organization_name'],
			'password'						=> Hash::make('P@ssw0rd!@#'),
		]);

		return redirect()->route('admin.voters.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function update(Request $request, User $voter)
	{
		$data = $request->validate([
			'first_name'          => 'required|string|max:255',
			'last_name'           => 'required|string|max:255',
			'organization_name'   => 'required|string|max:255',
			'phone_number'				=> 'required|string',
			'member_id'					 	=> 'required|string',
			'admin_id'       			=> 'required|string',
			'password'       			=> 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		$voter->update([
			'last_name'          	=> $data['last_name'],
			'first_name' 				 	=> $data['first_name'],
			'phone_number'				=> $data['phone_number'],
			'member_id'					 	=> $data['member_id'],
			'organization_name'		=> $data['organization_name'],
		]);

		return redirect()->route('admin.voters.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function destroy(Request $request, User $voter)
	{
		$request->validate([
			'admin_id' => 'required|string',
			'password' => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($request->input('admin_id'), $request->input('password'));

		$voter->delete();

		return back()->with([
			'success' => 'Removed Successfully',
			'buttonText' => 'Proceed'
		]);
	}
}
