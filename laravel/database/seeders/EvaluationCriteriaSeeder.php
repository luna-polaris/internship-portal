<?php

namespace Database\Seeders;

use App\Models\EvaluationCriterion;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Default rubric — weights total 100.
 * Run with: php artisan db:seed --class=EvaluationCriteriaSeeder
 */
class EvaluationCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'Admin')->value('user_id');

        $criteria = [
            ['Technical Competency',  'Technical',       20, 'Applies course knowledge and tools to real tasks.'],
            ['Quality of Work',       'Technical',       20, 'Output is accurate, complete and needs little rework.'],
            ['Communication',         'Soft Skills',     15, 'Explains progress and asks for help clearly, in writing and in person.'],
            ['Teamwork',              'Soft Skills',     15, 'Works well with colleagues and contributes to shared goals.'],
            ['Initiative',            'Soft Skills',     10, 'Looks for work and suggests improvements without being asked.'],
            ['Punctuality',           'Professionalism', 10, 'Arrives on time and meets agreed deadlines.'],
            ['Attitude and Ethics',   'Professionalism', 10, 'Professional conduct, respects confidentiality and workplace rules.'],
        ];

        foreach ($criteria as [$name, $category, $weight, $description]) {
            EvaluationCriterion::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'category'    => $category,
                    'max_score'   => 5,
                    'weight'      => $weight,
                    'is_active'   => true,
                    'created_by'  => $adminId,
                ]
            );
        }
    }
}