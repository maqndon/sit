<?php

namespace Tests\Traits;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Arr;

trait UserTrait
{
    protected ?User $user = null;

    protected ?Project $project = null;

    protected ?Task $task = null;

    /**
     * Create a regular user.
     */
    public function createUser(array $attributes = []): User
    {
        return $this->user = User::factory()->create($attributes);
    }

    /**
     * Create an admin user.
     */
    public function createAdminUser(): User
    {
        return $this->createUser(['role' => 'admin']);
    }

    /**
     * Create a project associated with a user.
     */
    public function createProject(array $attributes, int $projectCount)
    {
        return Project::factory()->count($projectCount)->create($attributes);
    }

    /**
     * Create a task associated with a user or project.
     */
    public function createTask(array $attributes, int $taskCount)
    {
        return Task::factory()->count($taskCount)->create($attributes);
    }

    /**
     * Create a user with an associated task.
     */
    public function createUserWithTasks(int $taskCount = 1): User
    {
        $user = $this->createUser();

        $this->createTask(['user_id' => $user->id], $taskCount);

        return $user;
    }

    /**
     * Create a user with an associated task.
     */
    public function createUserWithOverdueTask(int $taskCount = 1): User
    {
        $user = $this->createUser();
        $this->createTask([
            'user_id' => $user->id,
            'status' => (Arr::random(['todo', 'in_progress'])),
            'deadline' => now()->subDays(rand(1, 60)),
        ],
            $taskCount);

        return $user;
    }

    /**
     * Create a user with an associated project.
     */
    public function createUserWithProjects(int $projectCount = 1): User
    {
        $user = $this->createUser();
        $this->createProject(['user_id' => $user->id], $projectCount);

        return $user;
    }

    /**
     * Create a user with a project and a specified number of tasks.
     */
    public function createUserWithProjectAndTasks(int $taskCount = 1): User
    {
        $user = $this->createUser();
        $projectCount = 1;
        $project = $this->createProject(['user_id' => $user->id], $projectCount);
        $project = $project->first(); // first from the collection

        Task::factory($taskCount)->create(['user_id' => $user->id, 'project_id' => $project->id]);

        return $user;
    }
}
