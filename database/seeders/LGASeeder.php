<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LGASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lgas = [
            ['name' => 'Abeokuta North', 'designation' => 'ogun'],
            ['name' => 'Abeokuta South', 'designation' => 'ogun'],
            ['name' => 'Ado-Odo/Ota', 'designation' => 'ogun'],
            ['name' => 'Ewekoro', 'designation' => 'ogun'],
            ['name' => 'Ifo', 'designation' => 'ogun'],
            ['name' => 'Ijebu East', 'designation' => 'ogun'],
            ['name' => 'Ijebu North', 'designation' => 'ogun'],
            ['name' => 'Ijebu North East', 'designation' => 'ogun'],
            ['name' => 'Ijebu Ode', 'designation' => 'ogun'],
            ['name' => 'Ikenne', 'designation' => 'ogun'],
            ['name' => 'Imeko Afon', 'designation' => 'ogun'],
            ['name' => 'Ipokia', 'designation' => 'ogun'],
            ['name' => 'Obafemi Owode', 'designation' => 'ogun'],
            ['name' => 'Odeda', 'designation' => 'ogun'],
            ['name' => 'Odogbolu', 'designation' => 'ogun'],
            ['name' => 'Ogun Waterside', 'designation' => 'ogun'],
            ['name' => 'Remo North', 'designation' => 'ogun'],
            ['name' => 'Sagamu', 'designation' => 'ogun'],
            ['name' => 'Yewa North', 'designation' => 'ogun'],
            ['name' => 'Yewa South', 'designation' => 'ogun'],
        ];

        $data = array_map(function ($lga) {
            return [
                'name' => $lga['name'],
                'designation' => $lga['designation'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $lgas);

        DB::table('lgas')->insert($data);
    }
}
