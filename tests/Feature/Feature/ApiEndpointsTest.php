<?php

use App\Models\Task;
use App\Models\User;
use Tests\Traits\UserTrait;

uses(UserTrait::class);

it('allows authenticated users to view their tasks', function () {
    // Arrange: Create a user and their tasks
    $user = $this->createUserWithTask();

    // Act & Assert: Authenticated user can see their tasks
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonCount(1);
});

it('denies unauthenticated users access to tasks', function () {
    // Act: Try to access tasks without authentication
    $response = $this->getJson('/api/tasks');

    // Assert: Ensure it returns 401 Unauthorized
    $response->assertStatus(401);
});

it('allows authenticated users to create a task', function () {
    // Arrange: Create a user
    $user = $this->createUser ();
    $taskData = ['title' => 'New Task', 'description' => 'Task description', 'status' => 'todo'];

    // Act: The authenticated user creates a new task
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', $taskData);

    // Assert: Ensure the task is created successfully
    $response->assertStatus(201)
        ->assertJsonFragment($taskData);
});

it('allows authenticated users to update their task', function () {
    // Arrange: Create a user and a task
    $user = $this->createUserWithTask();
    $task = Task::first(); // Get the created task
    $updatedData = ['title' => 'Updated Task', 'description' => 'Updated description'];

    // Act: The authenticated user updates the task
    $response = $this->actingAs($user, 'sanctum')->putJson("/api/tasks/{$task->id}", $updatedData);

    // Assert: Ensure the task is updated successfully
    $response->assertStatus(200)
        ->assertJsonFragment($updatedData);
});

it('allows authenticated users to delete their task', function () {
    // Arrange: Create a user and a task
    $user = $this->createUserWithTask();
    $task = Task::first(); // Get the created task

    // Act: The authenticated user deletes the task
    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/tasks/{$task->id}");

    // Assert: Ensure the task is deleted successfully
    $response->assertStatus(204);
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});