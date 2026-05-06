<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Programme;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programmes = [
            ['name' => 'Cash Transfer', 'description' => 'Monthly cash support for qualified households'],
            ['name' => 'Food Support', 'description' => 'Monthly food baskets for households with children under 5 years old'],
            ['name' => 'Healthcare Subsidy', 'description' => 'Free access to basic healthcare at designated centers'],
            ['name' => 'School Support Grant', 'description' => 'Fees, uniforms, and materials for school-aged children'],
        ];

        $data = array_map(function ($programme) {
            return [
                'name' => $programme['name'],
                'description' => $programme['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $programmes);

        DB::table('programmes')->insert($data);
    }
}
