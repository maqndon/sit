<?php

namespace Tests\Traits;

use App\Models\Task;
use App\Models\User;

trait UserTrait
{
    protected $user;

    protected $task;

    public function createUser(): User
    {
        return $this->user = User::factory()->create();
    }

    public function createUserWithTask(): User
    {
        $this->user = User::factory()->create();
        Task::factory()->create(['user_id' => $this->user->id]);

        return $this->user;
    }
}
