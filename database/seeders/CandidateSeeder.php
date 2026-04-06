<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electionId = Election::findOrFail(1)->id;
        $candidatesPerPosition = 1;
        $organizationLabels = ['IT', 'HR', 'Marketing'];
        $organizationIds = [];
        foreach ($organizationLabels as $label) {
            $organizationIds[$label] = Organization::create(['name' => $label])->id;
        }

        $positionNames = Position::pluck('name');

        $positionIds = Position::whereIn('name', $positionNames)
            ->pluck('id', 'name');

        $firstNames = [
            'Adrian',
            'Mark',
            'Joshua',
            'Christian',
            'Daniel',
            'Miguel',
            'Paolo',
            'Carlo',
            'James',
            'Nathan',
            'Angela',
            'Sophia',
            'Nicole',
            'Jasmine',
            'Camille',
            'Andrea',
            'Katrina',
            'Clarisse',
            'Patricia',
            'Isabella',
            'Ethan',
            'Lance',
            'Kyle',
            'Gabriel',
            'Jared',
            'Hannah',
            'Bianca',
            'Alexa',
            'Trisha',
            'Maxine',
        ];

        $lastNames = [
            'Santos',
            'Reyes',
            'Cruz',
            'Bautista',
            'Flores',
            'Gonzales',
            'Mendoza',
            'Torres',
            'Castillo',
            'Ramirez',
            'Fernandez',
            'Aquino',
            'Navarro',
            'Villanueva',
            'Domingo',
            'De Leon',
            'Morales',
            'Velasco',
            'Ramos',
            'Gutierrez',
        ];

        foreach ($organizationLabels as $org) {
            foreach ($positionNames as $positionName) {
                $positionId = $positionIds[$positionName] ?? null;
                if (! $positionId) {
                    continue;
                }

                for ($i = 0; $i < $candidatesPerPosition; $i++) {
                    $first = $firstNames[array_rand($firstNames)];
                    $last = $lastNames[array_rand($lastNames)];

                    Candidate::create([
                        'election_id' => $electionId,
                        'position_id' => $positionId,
                        'organization_id' => $organizationIds[$org],
                        'first_name' => $first,
                        'last_name' => $last,
                    ]);
                }
            }
        }
    }
}
