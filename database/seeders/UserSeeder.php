<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
		User::firstOrCreate(['admin_id' => '202510010001'], [
			'last_name'			=> 'Admin',
			'first_name'		=> 'Pasei System',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09426735530',
			'type' 					=> UserType::SYSTEM_ADMIN->value,
		]);

		// Admin
		User::firstOrCreate(['admin_id' => '202510010002'], [
			'last_name'			=> 'Admin',
			'first_name'		=> 'Pasei',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09426735530',
			'type' 					=> UserType::ADMIN->value,
		]);

		// Voters
		User::firstOrCreate(['member_id' => '202510010003'], [
			'last_name'			=> 'Dela Cruz',
			'first_name'		=> 'Juan',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09426332392',
			'type' 					=> UserType::VOTER->value,
		]);

		User::firstOrCreate(['member_id' => '202510010004'], [
			'last_name'			=> 'Reyes',
			'first_name'		=> 'Maria',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09426332392',
			'type' 					=> UserType::VOTER->value,
		]);

		User::firstOrCreate(['member_id' => '202510010005'], [
			'last_name'			=> 'Santos',
			'first_name'		=> 'Pedro',
			'password'			=> Hash::make('P@ssw0rd!@#'),
			'phone_number'	=> '09761508455',
			'type' 					=> UserType::VOTER->value,
		]);
	}
}
