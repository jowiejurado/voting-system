<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\User;
use App\Models\UserSecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
			'first_name'            							=> 'required|string|max:255',
			'last_name'             							=> 'required|string|max:255',
			'organization_name'     							=> 'required|string|max:255',
			'phone_number'          							=> 'required|string',
			'email'          											=> 'required|string',
			'admin_id'              							=> 'required|string',
			'password'              							=> 'required|string',
			'face_descriptor_json'  							=> 'required|string',
			'security_questions'                  => 'required|array|min:1|max:3',
			'security_questions.*.question'       => 'required|string|max:255',
			'security_questions.*.answer'         => 'required|string|min:2|max:255',
		]);

		assert_current_user_is_admin();
		assert_admin_credentials($data['admin_id'], $data['password']);

		// Face descriptor (unchanged)
		$descriptor = null;
		if (!empty($data['face_descriptor_json'])) {
			try {
				$arr = json_decode($data['face_descriptor_json'], true, 512, JSON_THROW_ON_ERROR);
				if (is_array($arr) && count($arr) === 128 && array_reduce($arr, fn($ok, $v) => $ok && is_numeric($v), true)) {
					$descriptor = array_map('floatval', $arr);
				}
			} catch (\Throwable $e) {
			}
		}
		if (!$descriptor) {
			return back()->withErrors(['face_descriptor_json' => 'Please capture a face.'])->withInput();
		}

		DB::beginTransaction();
		try {
			$user = \App\Models\User::create([
				'last_name'         => $data['last_name'],
				'first_name'        => $data['first_name'],
				'phone_number'      => $data['phone_number'],
				'email'				      => $data['email'],
				'member_id'         => generate_member_id(),
				'organization_name' => $data['organization_name'],
				'password'          => \Illuminate\Support\Facades\Hash::make('P@ssw0rd!@#'),
				'face_descriptor'   => $descriptor,
			]);

			// Save up to 3 security questions
			foreach ($data['security_questions'] as $qa) {
				$q = trim($qa['question']);
				$a = \App\Models\UserSecurityQuestion::normalizeAnswer($qa['answer']);
				UserSecurityQuestion::create([
					'user_id'     => $user->id,
					'question'    => $q,
					'answer_hash' => \Illuminate\Support\Facades\Hash::make($a),
				]);
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			throw $e;
		}

		return redirect()->route('admin.voters.index')->with([
			'success' => 'Successfully Submitted',
			'buttonText' => 'Proceed'
		]);
	}

	public function update(Request $request, User $voter)
	{
		$data = $request->validate([
			'first_name'            => 'required|string|max:255',
			'last_name'             => 'required|string|max:255',
			'organization_name'     => 'required|string|max:255',
			'phone_number'          => 'required|string',
			'email'     			     	=> 'required|string',
			'member_id'             => 'required|string',
			'admin_id'              => 'required|string',
			'password'              => 'required|string',
			'face_descriptor_json'  => 'nullable|string',

			// NEW:
			'security_questions'                  => 'required|array|min:1|max:3',
			'security_questions.*.question'       => 'required|string|max:255',
			'security_questions.*.answer'         => 'required|string|min:2|max:255',
		]);

		assert_current_user_is_admin();
		assert_admin_credentials($data['admin_id'], $data['password']);

		$descriptor = $voter->face_descriptor;

		if (!empty($data['face_descriptor_json'])) {
			try {
				$arr = json_decode($data['face_descriptor_json'], true, 512, JSON_THROW_ON_ERROR);
				$is128 = is_array($arr) && count($arr) === 128;
				$allNum = $is128 && array_reduce($arr, fn($ok, $v) => $ok && is_numeric($v), true);
				if ($is128 && $allNum) {
					$descriptor = array_map('floatval', $arr);
				} else {
					return back()->withErrors(['face_descriptor_json' => 'Invalid face descriptor format.'])->withInput();
				}
			} catch (\Throwable $e) {
				return back()->withErrors(['face_descriptor_json' => 'Invalid face descriptor JSON.'])->withInput();
			}
		}

		DB::beginTransaction();
		try {
			$voter->update([
				'last_name'         => $data['last_name'],
				'first_name'        => $data['first_name'],
				'phone_number'      => $data['phone_number'],
				'email'   				  => $data['email'],
				'member_id'         => $data['member_id'],
				'organization_name' => $data['organization_name'],
				'face_descriptor'   => $descriptor,
			]);

			// Replace existing questions
			$voter->securityQuestions()->delete();

			foreach ($data['security_questions'] as $qa) {
				$q = trim($qa['question']);
				$a = \App\Models\UserSecurityQuestion::normalizeAnswer($qa['answer']);
				UserSecurityQuestion::create([
					'user_id'     => $voter->id,
					'question'    => $q,
					'answer_hash' => \Illuminate\Support\Facades\Hash::make($a),
				]);
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			throw $e;
		}

		return redirect()->route('admin.voters.index')->with([
			'success' => 'Successfully Updated',
			'buttonText' => 'Proceed'
		]);
	}
}
