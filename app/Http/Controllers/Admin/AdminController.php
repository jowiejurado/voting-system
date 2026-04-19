<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

		// `first_name` / `last_name` are encrypted at rest; filter decrypted values when searching.
		if ($q === '') {
			$admins = User::query()
				->where('is_active', true)
				->whereIn('type', ['admin', 'system-admin'])
				->latest()
				->paginate($perPage)
				->withQueryString();
		} else {
			$filtered = User::query()
				->where('is_active', true)
				->whereIn('type', ['admin', 'system-admin'])
				->latest()
				->get()
				->filter(function (User $user) use ($q) {
					if (mb_stripos((string) $user->first_name, $q) !== false) {
						return true;
					}
					if (mb_stripos((string) $user->last_name, $q) !== false) {
						return true;
					}
					if (mb_stripos(trim((string) $user->first_name . ' ' . (string) $user->last_name), $q) !== false) {
						return true;
					}
					if (mb_stripos((string) $user->admin_id, $q) !== false) {
						return true;
					}

					return false;
				})
				->values();

			$page = max(1, (int) $request->get('page', 1));
			$admins = new LengthAwarePaginator(
				$filtered->slice(($page - 1) * $perPage, $perPage)->values(),
				$filtered->count(),
				$perPage,
				$page,
				['path' => $request->url(), 'query' => $request->query()]
			);
		}

		return view('admin.index', compact('admins', 'q', 'perPage'));
	}

	public function store(Request $request)
	{
		if (!Auth::check() || Auth::user()->type !== 'system-admin') {
			return redirect()->route('admin.index')
				->with([
					'error' => 'Unauthorized',
					'buttonText' => 'Proceed'
				]);
		}

		// CHANGED: read the custom hidden field user_type
		$userType = $request->input('user_type');

		if (!in_array($userType, ['admin', 'system-admin'], true)) {
			abort(400, 'Invalid user type.');
		}

		// ===== SYSTEM ADMIN BRANCH =====
		if ($userType === 'system-admin') {
			$data = $request->validate([
				'first_name'   => 'required|string|max:255',
				'last_name'    => 'required|string|max:255',
				'phone_number' => 'required|string',
				'email' 			 => 'required|string',
				// optional, but safe to validate:
				'user_type'    => 'required|in:system-admin',
			]);

			User::create([
				'last_name'      => $data['last_name'],
				'first_name'     => $data['first_name'],
				'phone_number'   => $data['phone_number'],
				'email'   			 => $data['email'],
				// use your existing helper for IDs
				'admin_id'       => generate_system_admin_id(),
				'type'           => 'system-admin',
				'password'       => Hash::make('P@ssw0rd!@#'),
				'face_descriptor' => null,
			]);

			return redirect()->route('admin.index')
				->with([
					'success' => 'System admin successfully created',
					'buttonText' => 'Proceed'
				]);
		}

		// ===== ADMIN BRANCH =====
		$data = $request->validate([
			'first_name'           => 'required|string|max:255',
			'last_name'            => 'required|string|max:255',
			'phone_number'         => 'required|string',
			'email'         			 => 'required|string',
			'admin_id'             => 'required|string',   // current system-admin credentials
			'password'             => 'required|string',
			'face_descriptor_json' => 'required|string',
			'user_type'            => 'required|in:admin',
		]);

		assert_current_user_is_admin();
		assert_admin_credentials($data['admin_id'], $data['password']);

		$descriptor = null;
		if (!empty($data['face_descriptor_json'])) {
			try {
				$arr = json_decode($data['face_descriptor_json'], true, 512, JSON_THROW_ON_ERROR);
				if (
					is_array($arr)
					&& count($arr) === 128
					&& array_reduce($arr, fn($ok, $v) => $ok && is_numeric($v), true)
				) {
					$descriptor = array_map('floatval', $arr);
				}
			} catch (\Throwable $e) {
				// ignore, treat as no descriptor
			}
		}

		if (!$descriptor) {
			return back()->withErrors(['face_descriptor_json' => 'Please capture a face.'])->withInput();
		}

		User::create([
			'last_name'      => $data['last_name'],
			'first_name'     => $data['first_name'],
			'phone_number'   => $data['phone_number'],
			'email'   			 => $data['email'],
			'admin_id'       => generate_admin_id(),
			'type'           => 'admin',
			'password'       => Hash::make('P@ssw0rd!@#'),
			'face_descriptor' => $descriptor,
		]);

		return redirect()->route('admin.index')
			->with([
				'success' => 'Admin successfully created',
				'buttonText' => 'Proceed'
			]);
	}


	public function update(Request $request, User $admin)
	{
		if (!Auth::check() || Auth::user()->type !== 'system-admin') {
			return redirect()->route('admin.index')
				->with([
					'error' => 'Unauthorized',
					'buttonText' => 'Proceed'
				]);
		}

		if ($admin->type === 'system-admin') {
			$data = $request->validate([
				'first_name' => 'required|string|max:255',
				'last_name'  => 'required|string|max:255',
				'phone_number' => 'required|string',
				'email' => 'required|string',
			]);

			$admin->update([
				'last_name'  => $data['last_name'],
				'first_name' => $data['first_name'],
				'phone_number' => $data['phone_number'],
				'email' => $data['email'],
			]);

			return redirect()->route('admin.index')
				->with([
					'success' => 'System admin successfully updated',
					'buttonText' => 'Proceed'
				]);
		}

		$data = $request->validate([
			'first_name'           => 'required|string|max:255',
			'last_name'            => 'required|string|max:255',
			'phone_number'         => 'required|string',
			'email'         			 => 'required|string',
			'admin_id'             => 'required|string',
			'password'             => 'required|string',
			'face_descriptor_json' => 'nullable|string',
		]);

		assert_current_user_is_admin();
		assert_admin_credentials($data['admin_id'], $data['password']);

		$descriptor = $admin->face_descriptor;

		if (!empty($data['face_descriptor_json'])) {
			try {
				$arr = json_decode($data['face_descriptor_json'], true, 512, JSON_THROW_ON_ERROR);
				if (
					is_array($arr)
					&& count($arr) === 128
					&& array_reduce($arr, fn($ok, $v) => $ok && is_numeric($v), true)
				) {
					$descriptor = array_map('floatval', $arr);
				} else {
					return back()->withErrors([
						'face_descriptor_json' => 'Invalid face descriptor format.',
					])->withInput();
				}
			} catch (\Throwable $e) {
				return back()->withErrors([
					'face_descriptor_json' => 'Invalid face descriptor JSON.',
				])->withInput();
			}
		}

		$admin->update([
			'last_name'       => $data['last_name'],
			'first_name'      => $data['first_name'],
			'phone_number'    => $data['phone_number'],
			'email'    				=> $data['email'],
			'face_descriptor' => $descriptor,
		]);

		return redirect()->route('admin.index')
			->with([
				'success' => 'Admin successfully updated',
				'buttonText' => 'Proceed'
			]);
	}
}
