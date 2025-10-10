<?php

namespace Database\Seeders;

use App\Models\Election;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ElectionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		Election::updateOrCreate(
			['title' => 'PASEI Officers & Board of Directors 2025 - 2026'],
			[
				'date'       => Carbon::now()->toDateString(),
				'start_time' => Carbon::now()->subMinutes(5)->format('H:i:s'),
				'end_time'   => Carbon::now()->addHours(2)->format('H:i:s'),
				'is_active'  => true,
			]
		);
	}
}
