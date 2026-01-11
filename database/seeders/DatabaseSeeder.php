<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the AdminUserSeeder first
        $this->call([
            \Database\Seeders\AdminUserSeeder::class,
            \Database\Seeders\SampleUserSeeder::class, // New sample users
        ]);
        $this->call(PropertiesSeeder::class);
    }
}
