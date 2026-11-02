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
        $this->call([
            SubjectSeeder::class,
            ClassroomSeeder::class,
            UserSeeder::class,
            MaterialSeeder::class,
            ActivitySeeder::class,
        ]);
    }
}
