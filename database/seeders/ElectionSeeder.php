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
		Election::firstOrCreate([
			'title' 			=> 'PASEI Officers & Board of Directors 2025 - 2026',
		], [
			'date' 				=> Carbon::parse('2025-10-03'),
			'start_time' 	=> now()->format('h:i:s'),
			'end_time'		=> now()->addHours(8)->format('h:i:s'),
			'is_active' 	=> true
		]);
	}
}
