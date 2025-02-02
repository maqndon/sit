<?php

use App\Models\Task;
use Tests\Traits\UserTrait;

uses(UserTrait::class);

beforeEach(function () {
    $this->refreshDatabase();
});

it('denies unauthenticated users access to tasks', function () {
    // Act: Try to access tasks without authentication
    $response = $this->getJson('/api/tasks');

    // Assert: Ensure it returns 401 Unauthorized
    $response->assertStatus(401);
});

it('allows authenticated users to view all projects tasks', function () {
    // Arrange: Create a user and their tasks
    $user = $this->createUserWithTask();

    // Act & Assert: Authenticated user can see their tasks
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

    $response
        ->assertStatus(200)
        ->assertJsonCount(1);
});

it('allows authenticated users to view their tasks', function () {
    // Arrange: Create a user and their tasks
    $user = $this->createUserWithTask();

    // Act & Assert: Authenticated user can see their tasks
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

    $response
        ->assertStatus(200)
        ->assertJsonCount(1);
});

it('allows authenticated users to create a task', function () {
    // Arrange: Create a user
    $user = $this->createUser();
    $taskData = [
        'title' => 'New Task',
        'description' => 'Task description',
        'status' => 'todo',
    ];

    // Act: The authenticated user creates a new task
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', $taskData);

    // Assert: Ensure the task is created successfully
    $response
        ->assertStatus(201)
        ->assertJsonFragment($taskData);

    $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
});

it('allows authenticated users to update their task', function () {
    // Arrange: Create a user and a task
    $user = $this->createUserWithTask();
    $task = Task::first();  // Get the created task
    $updatedData = ['title' => 'Updated Task', 'description' => 'Updated description'];

    // Act: The authenticated user updates the task
    $response = $this->actingAs($user, 'sanctum')->putJson("/api/tasks/{$task->id}", $updatedData);

    // Assert: Ensure the task is updated successfully
    $response
        ->assertStatus(200)
        ->assertJsonFragment($updatedData);
});

it('allows authenticated users to delete their task', function () {
    // Arrange: Create a user and a task
    $user = $this->createUserWithTask();
    $task = Task::first();  // Get the created task

    // Act: The authenticated user deletes the task
    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/tasks/{$task->id}");

    // Assert: Ensure the task is deleted successfully
    $response->assertStatus(204);
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

it('returns 422 for invalid task creation', function () {
    // Arrange: Create a user
    $user = $this->createUser();

    // Act: The authenticated user attempts to create a task with invalid data
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
        'title' => '',  // Empty title
        'description' => 'Task description',
        'status' => 'invalid_status',  // Invalid status
    ]);

    // Assert: Expect a 422 Unprocessable Entity status and a JSON structure for errors
    $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);
});

it('denies unauthorized user from deleting a task', function () {
    // Arrange: Create a user with a task and another user
    $user = $this->createUserWithTask();
    $otherUser = $this->createUser();  // Another user
    $task = Task::first();  // Get the first task

    // Act: The unauthorized user attempts to delete the task
    $response = $this->actingAs($otherUser, 'sanctum')->deleteJson("/api/tasks/{$task->id}");

    // Assert: Expect a 403 Forbidden status
    $response->assertForbidden();
});

it('allows authenticated users to view tasks of a specific project', function () {
    // Arrange: Create a user with a project and tasks
    $user = $this->createUserWithProjectAndTasks(3);
    $project = $user->projects()->first();  // Get the first project of the user

    // Act: The authenticated user requests the tasks of the project
    $response = $this->actingAs($user, 'sanctum')->getJson("/api/projects/{$project->id}/tasks");

    // Assert: Verify that the user can see the project's tasks
    $response->assertStatus(200)->assertJsonCount(3);
});

it('allows admin users to view all tasks', function () {
    // Arrange: Create an admin user and some tasks for different users
    $adminUser = $this->createAdminUser();
    $user = $this->createUserWithTask();  // Create a regular user with a task
    $otherTask = Task::factory()->for($this->user)->create();  // Create another task for the same user

    // Act: The admin user retrieves all tasks
    $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/tasks');

    // Assert: Ensure the response is successful and contains the tasks
    $response
        ->assertStatus(200)
        ->assertJsonCount(2);  // Assuming there are 2 tasks in total
});

it('allows admin users to update any task', function () {
    // Arrange: Create an admin user and a task for a regular user
    $adminUser = $this->createAdminUser();
    $user = $this->createUserWithTask();  // Create a regular user with a task
    $task = Task::first();  // Get the created task
    $updatedData = ['title' => 'Admin Updated Task', 'description' => 'Updated by admin'];

    // Act: The admin user updates the task
    $response = $this->actingAs($adminUser, 'sanctum')->putJson("/api/tasks/{$task->id}", $updatedData);

    // Assert: Ensure the task is updated successfully
    $response
        ->assertStatus(200)
        ->assertJsonFragment($updatedData);
});

it('allows admin users to delete any task', function () {
    // Arrange: Create an admin user and a task for a regular user
    $adminUser = $this->createAdminUser();
    $user = $this->createUserWithTask();  // Create a regular user with a task
    $task = Task::first();  // Get the created task

    // Act: The admin user deletes the task
    $response = $this->actingAs($adminUser, 'sanctum')->deleteJson("/api/tasks/{$task->id}");

    // Assert: Ensure the task is deleted successfully
    $response->assertStatus(204);
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});
