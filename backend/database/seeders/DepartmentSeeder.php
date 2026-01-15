<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'children' => [
                    ['name' => 'Recruitment'],
                    ['name' => 'Training & Development'],
                    ['name' => 'Payroll'],
                ],
            ],
            [
                'name' => 'Information Technology',
                'children' => [
                    ['name' => 'Software Development'],
                    ['name' => 'Infrastructure'],
                    ['name' => 'IT Support'],
                ],
            ],
            [
                'name' => 'Finance',
                'children' => [
                    ['name' => 'Accounting'],
                    ['name' => 'Budgeting'],
                ],
            ],
            [
                'name' => 'Marketing',
                'children' => [
                    ['name' => 'Digital Marketing'],
                    ['name' => 'Brand Management'],
                ],
            ],
            [
                'name' => 'Operations',
                'children' => [
                    ['name' => 'Logistics'],
                    ['name' => 'Quality Assurance'],
                ],
            ],
        ];

        foreach ($departments as $dept) {
            $parent = Department::firstOrCreate(
                ['name' => $dept['name'], 'parent_id' => null]
            );

            if (isset($dept['children'])) {
                foreach ($dept['children'] as $child) {
                    Department::firstOrCreate([
                        'name' => $child['name'],
                        'parent_id' => $parent->id,
                    ]);
                }
            }
        }

        $this->command->info('Departments seeded successfully! (' . Department::count() . ' departments)');
    }
}
