<?php

namespace Tests\Traits;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

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
    public function createProject(array $attributes = []): Project
    {
        return $this->project = Project::factory()->create($attributes);
    }

    /**
     * Create a task associated with a user or project.
     */
    public function createTask(array $attributes = []): Task
    {
        return $this->task = Task::factory()->create($attributes);
    }

    /**
     * Create a user with an associated task.
     */
    public function createUserWithTask(): User
    {
        $user = $this->createUser();
        $this->createTask(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Create a user with an associated project.
     */
    public function createUserWithProject(): User
    {
        $user = $this->createUser();
        $this->createProject(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Create a user with a project and a specified number of tasks.
     */
    public function createUserWithProjectAndTasks(int $taskCount = 1): User
    {
        $user = $this->createUser();
        $project = $this->createProject(['user_id' => $user->id]);

        Task::factory($taskCount)->create(['user_id' => $user->id, 'project_id' => $project->id]);

        return $user;
    }
}
