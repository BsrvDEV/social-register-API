<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = [

            [
                'name' => 'Brookes Admin',
                'last_name' => 'Admin ',
                'first_name' => 'Brookes ',
                'email' => 'brookes@ogsg.com',
                // 'phone' => '09011122345',
                'registration_type' => 'admin',
                'password' => Hash::make('brookes@123'),
                'created_at' => now(),
                'updated_at' => now(),
                'is_active' => true
                // 'otp' => Hash::make('12345'),
            ],

        ];

        // Insert the data into the levels table
        DB::table('users')->insert($admin);
    }
}
