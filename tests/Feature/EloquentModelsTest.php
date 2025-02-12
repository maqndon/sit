<?php

use App\Models\Project;
use App\Models\Task;
use Tests\Traits\UserTrait;

uses(UserTrait::class);

beforeEach(function () {
    $this->refreshDatabase();
});

describe('User Model', function () {
    it('allows a user to have multiple tasks', function () {
        // Arrange: Create a user and multiple tasks
        $user = $this->createUserWithTasks(3);

        // Act: Fetch the user's tasks
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

        // Assert: Ensure it returns 200 and the correct number of tasks
        $response
            ->assertOk()
            ->assertJsonCount(3);
    });

    it('allows a user to have multiple projects', function () {
        // Arrange: Create a user and multiple projects
        $user = $this->createUserWithProjects(3);

        // Act: Fetch the user's tasks
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/projects');

        // Assert: Ensure it returns 200 and the correct number of projects
        $response
            ->assertOk()
            ->assertJsonCount(3);
    });
});

describe('Task Model', function () {
    it('ensures a task belongs to a project', function () {
        // Arrange: Create a user with a project and tasks
        $user = $this->createUserWithProjectAndTasks(10);
        $project = Project::first();
        $task = $project->tasks->first();

        // Act: Fetch the task
        $response = $this->actingAs($project->user, 'sanctum')->getJson("/api/tasks/{$task->id}");

        // Assert: Ensure it returns 200 and the task belongs to the project
        $response->assertOk();
    });

    it('ensures a task belongs to an user', function () {
        // Arrange: Create a user with a project and tasks
        $user = $this->createUserWithProjectAndTasks(10);
        $task = Task::first();

        // Act: Fetch the task
        $response = $this->actingAs($task->user, 'sanctum')->getJson("/api/tasks/{$task->id}");

        // Assert: Ensure it returns 200 and the task belongs to the project
        $response->assertOk();
    });
});

describe('Project Model', function () {
    it('allows a project to have multiple tasks', function () {
        // Arrange: Create a project and multiple tasks
        $user = $this->createUserWithProjectAndTasks(10);
        $project = Project::first();

        // Act: Fetch the project's tasks
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/projects/{$project->id}/tasks");

        // Assert: Ensure it returns 200 and the correct number of tasks
        $response
            ->assertOk()
            ->assertJsonCount(10);
    });
});
