<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin 1
        User::updateOrCreate(
            ['email' => 'admin1@cf.com'],
            [
                'name' => 'Admin One',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'), // change after first login
                'remember_token' => Str::random(10),
            ]
        );

        // Admin 2
        User::updateOrCreate(
            ['email' => 'admin2@cf.com'],
            [
                'name' => 'Admin Two',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'), // change after first login
                'remember_token' => Str::random(10),
            ]
        );
    }
}
