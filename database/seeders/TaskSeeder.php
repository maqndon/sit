<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // adding 5 tasks to the admin user for testing
        Task::factory(5)->create([
            'user_id' => 1,
        ]);

        Task::factory()->count(50)->create();
    }
}
