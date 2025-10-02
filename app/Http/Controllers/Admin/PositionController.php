<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PositionController extends Controller
{
	public function index()
	{
		$positions = Position::orderBy('id')->get();
		return view('admin.positions.index', compact('positions'));
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'name'           => 'required|string|max:255',
			'maximum_votes'  => 'required|integer|min:1',
			'admin_id'       => 'required|string',
			'password'       => 'required|string',
		]);

		$this->assertCurrentUserIsAdmin();
		$this->assertAdminCredentials($data['admin_id'], $data['password']);

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

		$this->assertCurrentUserIsAdmin();
		$this->assertAdminCredentials($data['admin_id'], $data['password']);

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

		$this->assertCurrentUserIsAdmin();
    $this->assertAdminCredentials($request->admin_id, $request->password);

		$position->delete();

		return back()->with([
			'success' => 'Deleted',
			'buttonText' => 'Proceed'
		]);
	}

	private function assertCurrentUserIsAdmin(): void
	{
		if (!Auth::check() || !in_array(strtolower(Auth::user()->type), ['admin', 'system-admin'], true)) {
			abort(403, 'Unauthorized');
		}
	}

	private function assertAdminCredentials(string $adminId, string $password): void
	{
		$user = User::where('admin_id', $adminId)->first();

		if (!$user || !Hash::check($password, $user->password)) {
			back()
				->withErrors(['admin_id' => 'Invalid admin credentials', 'password' => ' '])
				->withInput(request()->except('password'))
				->throwResponse();
		}

		if (!in_array(strtolower($user->type), ['admin', 'system-admin'], true)) {
			back()
				->withErrors(['admin_id' => 'Only admin or system-admin may perform this action'])
				->withInput(request()->except('password'))
				->throwResponse();
		}
	}
}
