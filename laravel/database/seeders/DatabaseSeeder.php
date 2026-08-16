<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // AdminSeeder first — EvaluationCriteriaSeeder stamps the created_by
        // column with the admin's user_id.
        $this->call(AdminSeeder::class);
        $this->call(EvaluationCriteriaSeeder::class);
        $this->call(InternshipSeeder::class);
    }
}
