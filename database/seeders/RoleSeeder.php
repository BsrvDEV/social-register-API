<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = [

            ['name' => 'Zonal Officer', 'guard_name' => 'sanctum', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Zonal Reviewer', 'guard_name' => 'sanctum', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ICT Officer', 'guard_name' => 'sanctum', 'created_at' => now(), 'updated_at' => now()],

        ];

        DB::table('roles')->insert($admin);
    }
}
