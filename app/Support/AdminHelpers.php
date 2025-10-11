<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

if (! function_exists('assert_current_user_is_admin')) {
	/**
	 * Ensure the currently authenticated user is admin or system-admin.
	 * Aborts with 403 if not.
	 */
	function assert_current_user_is_admin(): void
	{
		if (!Auth::check() || !in_array(strtolower(Auth::user()->type), ['admin', 'system-admin'], true)) {
			abort(403, 'Unauthorized');
		}
	}
}

if (! function_exists('assert_admin_credentials')) {
	/**
	 * Validate a pair of admin credentials (admin_id + password) WITHOUT logging in the user.
	 * Redirects back with validation errors if invalid / not an allowed role.
	 */
	function assert_admin_credentials(string $adminId, string $password): void
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

		// Optional: require the re-auth to be the same as the current user
		// if ($user->id !== Auth::id()) {
		//     back()->withErrors(['admin_id' => 'Re-auth must match your account'])->throwResponse();
		// }
	}
}

if (! function_exists('generate_admin_id')) {
	function generate_admin_id(int $pad = 4): string
	{
		return DB::transaction(function () use ($pad) {
			$prefix = 'adm';

			// Lock rows to avoid duplicates under concurrency
			$last = DB::table('users')
				->where('admin_id', 'like', $prefix . '%')
				->orderByDesc('admin_id')
				->lockForUpdate()
				->value('admin_id');

			// Start at 0 if none exist yet
			$nextSeq = $last
				? (int) substr($last, strlen($prefix)) + 1
				: 0;

			return $prefix . str_pad((string) $nextSeq, $pad, '0', STR_PAD_LEFT);
		});
	}
}

if (! function_exists('generate_member_id')) {
	function generate_member_id(int $pad = 4): string
	{
		return DB::transaction(function () use ($pad) {
			$prefix = 'psi';

			// Lock rows to avoid duplicates under concurrency
			$last = DB::table('users')
				->where('member_id', 'like', $prefix . '%')
				->orderByDesc('member_id')
				->lockForUpdate()
				->value('member_id');

			// Start at 0 if none exist yet
			$nextSeq = $last
				? (int) substr($last, strlen($prefix)) + 1
				: 0;

			return $prefix . str_pad((string) $nextSeq, $pad, '0', STR_PAD_LEFT);
		});
	}
}
