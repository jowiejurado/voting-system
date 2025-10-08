<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
	public function showAdmin()
	{
		return view('admin.index');
	}

	public function index(Request $request)
	{
		$q = trim($request->get('q', ''));
		$perPage = (int) $request->get('per_page', 10);
		$perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

		$admins = \App\Models\User::query()
			->where('is_active', true)
			->where('type', 'admin')
			->orWhere('type', 'system-admin')
			->when($q !== '', function ($query) use ($q) {
				$query->where(function ($sub) use ($q) {
					$sub->where('first_name', 'like', "%{$q}%");
					$sub->orWhere('last_name', 'like', "%{$q}%");
					$sub->orWhere('admin_id', 'like', "%{$q}%");
				});
			})
			->latest()
			->paginate($perPage)
			->withQueryString();

		return view('admin.index', compact('admins', 'q', 'perPage'));
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'first_name'          => 'required|string|max:255',
			'last_name'           => 'required|string|max:255',
			'phone_number'				=> 'required|string',
			'admin_id'       			=> 'required|string',
			'password'       			=> 'required|string',
		]);

		if (Auth::user()->type != 'system-admin') {
			return redirect()->route('admin.index')
			->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Proceed'
			]);
		}

		assert_current_user_is_admin();
		assert_admin_credentials($data['admin_id'], $data['password']);

		User::create([
			'last_name'          	=> $data['last_name'],
			'first_name' 				 	=> $data['first_name'],
			'phone_number'				=> $data['phone_number'],
			'admin_id'					 	=> generate_admin_id(),
			'type'								=> 'admin',
			'password'						=> Hash::make('P@ssw0rd!@#'),
		]);

		return redirect()->route('admin.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function update(Request $request, User $admin)
	{
		$data = $request->validate([
			'first_name'          => 'required|string|max:255',
			'last_name'           => 'required|string|max:255',
			'phone_number'				=> 'required|string',
			'admin_id'       			=> 'required|string',
			'password'       			=> 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($data['admin_id'], $data['password']);

		$admin->update([
			'last_name'          	=> $data['last_name'],
			'first_name' 				 	=> $data['first_name'],
			'phone_number'				=> $data['phone_number'],
		]);

		return redirect()->route('admin.index')
			->with([
				'success' => 'Successfully Submitted',
				'buttonText' => 'Proceed'
			]);
	}

	public function destroy(Request $request, User $admin)
	{
		$request->validate([
			'admin_id' => 'required|string',
			'password' => 'required|string',
		]);

		assert_current_user_is_admin();
    assert_admin_credentials($request->input('admin_id'), $request->input('password'));

		$admin->forceFill(['is_active' => false])->save();

		return back()->with([
			'success' => 'Removed Successfully',
			'buttonText' => 'Proceed'
		]);
	}
}
