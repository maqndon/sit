<?php

use Illuminate\Support\Facades\DB;
use Tests\Traits\UserTrait;

uses(UserTrait::class);

beforeEach(function () {
    $this->refreshDatabase();
});

it('responds within an acceptable time', function () {
    // Arrange: Create a user with 100 tasks
    $user = $this->createUserWithTasks(100);

    // Act: Capture the time before the request and make the request
    $start = microtime(true); // Capture the time before the request
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks');
    $duration = microtime(true) - $start; // Calculate the duration

    // Assert: Ensure the response is OK and the duration is less than 500ms
    $response->assertOk();
    expect($duration)->toBeLessThan(0.5); // Ensure it takes less than 500ms
});

it('handles multiple requests without errors', function () {
    // Arrange: Create a user with 100 tasks
    $user = $this->createUserWithTasks(100);

    // Act: Send multiple requests and collect the responses
    $responses = collect(range(1, 10))->map(fn () => $this->actingAs($user, 'sanctum')->getJson('/api/tasks')
    );

    // Assert: Ensure each response is OK
    $responses->each(fn ($response) => $response->assertOk());
});

it('avoids N+1 queries on tasks endpoint', function () {
    // Arrange: Enable query logging and create a user with a task
    DB::enableQueryLog(); // Enable SQL query logging
    $user = $this->createUserWithTasks();

    // Act: Make a request to the tasks endpoint
    $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

    // Assert: Check the number of queries executed
    $queries = DB::getQueryLog(); // Get the executed queries
    expect(count($queries))->toBeLessThan(5); // Adjust according to the endpoint
});

it('avoids N+1 queries on projects endpoint', function () {
    // Arrange: Enable query logging and create a user with a project
    DB::enableQueryLog(); // Enable SQL query logging
    $user = $this->createUserWithProjects();

    // Act: Make a request to the projects endpoint
    $this->actingAs($user, 'sanctum')->getJson('/api/projects');

    // Assert: Check the number of queries executed
    $queries = DB::getQueryLog(); // Get the executed queries
    expect(count($queries))->toBeLessThan(5); // Adjust according to the endpoint
});

it('avoids N+1 queries on overdue tasks endpoint', function () {
    // Arrange: Enable query logging and create a user with a project
    DB::enableQueryLog(); // Enable SQL query logging
    $user = $this->createUserWithTasks();

    // Act: Make a request to the projects endpoint
    $this->actingAs($user, 'sanctum')->getJson('/api/tasks/overdue');

    // Assert: Check the number of queries executed
    $queries = DB::getQueryLog(); // Get the executed queries
    expect(count($queries))->toBeLessThan(6); // Adjust according to the endpoint
});

it('does not consume excessive memory', function () {
    // Arrange: Create a user with tasks
    $user = $this->createUserWithTasks(200);

    // Act: Get the memory usage before the request
    $startMemory = memory_get_usage(); // Memory before the request

    // Make a request to the tasks endpoint
    $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

    // Assert: Calculate the memory used and check if it's less than 5MB
    $usedMemory = memory_get_usage() - $startMemory; // Calculate memory consumption

    expect($usedMemory)->toBeLessThan(5 * 1024 * 1024); // Maximum 5MB
});
