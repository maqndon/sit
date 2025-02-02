<?php

use App\Models\Project;
use Tests\Traits\UserTrait;

uses(UserTrait::class);

beforeEach(function () {
    $this->refreshDatabase();
});

it('denies unauthenticated users access to projects', function () {
    // Act: Try to access projects without authentication
    $response = $this->getJson('/api/projects');

    // Assert: Ensure it returns 401 Unauthorized
    $response->assertStatus(401);
});

it('allows authenticated users to view their projects', function () {
    // Arrange: Create a user and their projects
    $user = $this->createUserWithProject();

    // Act & Assert: Authenticated user can see their projects
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/projects');

    // Assert:Ensure it returns 200
    $response
        ->assertStatus(200)
        ->assertJsonCount(1);
});

it('allows authenticated users to create a project', function () {
    // Arrange: Create a user
    $user = $this->createUser();
    $projectData = [
        'name' => 'New Project',
        'description' => 'Project description',
    ];

    // Act: The authenticated user creates a new project
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/projects', $projectData);

    // Assert: Ensure the Project is created successfully
    $response
        ->assertStatus(201)
        ->assertJsonFragment($projectData);
});

it('allows authenticated users to update their Project', function () {
    // Arrange: Create a user and a Project
    $user = $this->createUserWithProject();
    $project = Project::latest()->first();  // Get the last created Project
    $updatedData = [
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ];

    // Act: The authenticated user updates the Project
    $response = $this->actingAs($user, 'sanctum')->putJson("/api/projects/{$project->id}", $updatedData);

    // Assert: Ensure the Project is updated successfully
    $response
        ->assertStatus(200)
        ->assertJsonFragment($updatedData);
});

it('allows authenticated users to delete their project', function () {
    // Arrange: Create a user and a Project
    $user = $this->createUserWithProject();
    $project = Project::first();  // Get the created project

    // Act: The authenticated user deletes the Project
    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/projects/{$project->id}");

    // Assert: Ensure the Project is deleted successfully
    $response->assertStatus(204);
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

it('returns 422 for invalid Project creation', function () {
    // Arrange: Create a user
    $user = $this->createUser();

    // Act: The authenticated user attempts to create a Project with invalid data
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/projects', [
        'title' => '',  // Empty title
        'description' => 'Project description',
        'status' => 'invalid_status',  // Invalid status
    ]);

    // Assert: Expect a 422 Unprocessable Entity status and a JSON structure for errors
    $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);
});

it('denies unauthorized user from deleting a Project', function () {
    // Arrange: Create a user with a Project and another user
    $user = $this->createUserWithProject();
    $otherUser = $this->createUser();  // Another user
    $project = Project::first();  // Get the first Project

    // Act: The unauthorized user attempts to delete the Project
    $response = $this->actingAs($otherUser, 'sanctum')->deleteJson("/api/projects/{$project->id}");

    // Assert: Expect a 403 Forbidden status
    $response->assertForbidden();
});

it('allows admin users to view all projects', function () {
    // Arrange: Create an admin user and another user with projects
    $adminUser = $this->createAdminUser();
    $this->createUserWithProject();  // A user with a project

    // Act: The admin user retrieves all projects
    $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/projects');

    // Assert: Ensure the response contains all projects
    $response
        ->assertStatus(200)
        ->assertJsonCount(Project::count());  // Ensure it matches DB count
});

it('allows admin users to update any Project', function () {
    // Arrange: Create an admin user and a Project for a regular user
    $adminUser = $this->createAdminUser();
    $user = $this->createUserWithProject();  // Create a regular user with a Project
    $project = Project::first();  // Get the created Project
    $updatedData = [
        'name' => 'Admin Updated Project',
        'description' => 'Updated by admin',
    ];

    // Act: The admin user updates the Project
    $response = $this->actingAs($adminUser, 'sanctum')->putJson("/api/projects/{$project->id}", $updatedData);

    // Assert: Ensure the Project is updated successfully
    $response
        ->assertStatus(200)
        ->assertJsonFragment($updatedData);
});

it('allows admin users to delete any Project', function () {
    // Arrange: Create an admin user and a Project for a regular user
    $adminUser = $this->createAdminUser();
    $user = $this->createUserWithProject();  // Create a regular user with a Project
    $project = Project::first();  // Get the created Project

    // Act: The admin user deletes the Project
    $response = $this->actingAs($adminUser, 'sanctum')->deleteJson("/api/projects/{$project->id}");

    // Assert: Ensure the Project is deleted successfully
    $response->assertStatus(204);
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});
