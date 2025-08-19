<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the AdminUserSeeder to create admin users
        // $this->call(AdminUserSeeder::class);
        // $this->call(ProjectTypeSeeder::class);
        // $this->call(AccountCodeTypeSeeder::class);
        $this->call(AccountCodeSeeder::class);
        // You can add more seeders here as needed
        // $this->call(OtherSeeder::class);
    }
}
