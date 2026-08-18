<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // AdminSeeder must run first; EvaluationCriteriaSeeder stamps created_by with the admin's user_id.
        $this->call(AdminSeeder::class);
        $this->call(EvaluationCriteriaSeeder::class);
        $this->call(InternshipSeeder::class);
    }
}
