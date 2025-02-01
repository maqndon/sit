<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();  // Get all users
        $projects = Project::all();  // Get all projects

        // Ensure there are users and projects available
        if ($users->isEmpty() || $projects->isEmpty()) {
            return;  // Or throw an exception if necessary
        }

        // Create 50 tasks, each with a random user and project
        for ($i = 0; $i < 50; $i++) {
            Task::factory()->create([
                'user_id' => $users->random()->id,  // Assign a random user ID
                'project_id' => $projects->random()->id,  // Assign a random project ID
            ]);
        }
    }
}
