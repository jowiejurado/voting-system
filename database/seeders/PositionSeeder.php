<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		collect([
			'Chairperson',
			'President',
			'Vice President',
			'Corporate Secretary',
			'Treasurer',
			'Assistant Treasurer',
			'Auditor',
			'P.R.O',
			'Directors',
			'Executive Committee',
			'Research Committee',
			'Ways and Means',
			'External & Legal Affairs Committee',
		])->each(function ($name) {
			Position::create([
				'name' => $name,
			]);
		});
	}
}
