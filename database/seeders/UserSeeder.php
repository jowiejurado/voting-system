<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// System Admin
		User::firstOrCreate(['admin_id' => 'sadm0000'], [
			'last_name'			=> 'Admin',
			'first_name'		=> 'System',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09275517245',
			'type' 					=> UserType::SYSTEM_ADMIN->value,
		]);

		User::firstOrCreate(['admin_id' => 'sadm0001'], [
			'last_name'			=> 'Jurado',
			'first_name'		=> 'Jowie Trence',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09426735530',
			'type' 					=> UserType::SYSTEM_ADMIN->value,
		]);

		// Admin
		User::firstOrCreate(['admin_id' => 'adm0000'], [
			'last_name'			=> 'Sahagun',
			'first_name'		=> 'Enuj John',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09275517245',
			'type' 					=> UserType::ADMIN->value,
		]);

		User::firstOrCreate(['admin_id' => 'adm0001'], [
			'last_name'			=> 'Pascual',
			'first_name'		=> 'Harold Neil',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09666254818',
			'type' 					=> UserType::ADMIN->value,
		]);

		// Voters
		User::firstOrCreate(['member_id' => 'psi0000'], [
			'last_name'			=> 'Dela Cruz',
			'first_name'		=> 'Juan',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09275517245',
			'type' 					=> UserType::VOTER->value,
		]);
	}
}
