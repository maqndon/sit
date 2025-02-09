<?php

use App\Models\User;
use Tests\Traits\UserTrait;

uses(UserTrait::class);

beforeEach(function () {
    $this->refreshDatabase();
});

it('denies non-admin users access to the endpoint /api/users/', function () {
    // Arrange: Create a non-admin user
    $user = $this->createUser();

    // Act: Try to access the users endpoint as a non-admin
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/users');

    // Assert: Ensure it returns 403 Forbidden
    $response->assertForbidden();
});

it('denies unauthenticated users access to the users endpoint', function () {
    // Act: Try to access the users endpoint without authentication
    $response = $this->getJson('/api/users');

    // Assert: Ensure it returns 401 Unauthorized
    $response->assertUnauthorized();
});

it('allows admin users access to the endpoint /api/users/', function () {
    // Arrange: Create an admin user
    $admin = $this->createAdminUser();

    // Act: Try to access the users endpoint as an admin
    $response = $this->actingAs($admin, 'sanctum')->getJson('/api/users');

    // Assert: Ensure it returns 200 OK
    $response->assertOk();
});

it('denies non-admin users access to view another user', function () {
    // Arrange: Create a user and another user
    $user = $this->createUser();
    $anotherUser = $this->createUser();

    // Act: Try to access another user's profile as a non-admin
    $response = $this->actingAs($anotherUser, 'sanctum')->getJson("/api/users/{$user->id}");

    // Assert: Ensure it returns 403 Forbidden
    $response->assertForbidden();
});

it('allows admin users access to view another user', function () {
    // Arrange: Create a user and an admin user
    $user = $this->createUser();
    $admin = $this->createAdminUser();

    // Act: Try to access another user's profile as an admin
    $response = $this->actingAs($admin, 'sanctum')->getJson("/api/users/{$user->id}");

    // Assert: Ensure it returns 200 OK
    $response->assertOk();
});

it('allows users to view their own profile', function () {
    // Arrange: Create a user
    $user = $this->createUser();

    // Act: Try to access their own profile
    $response = $this->actingAs($user, 'sanctum')->getJson("/api/users/{$user->id}");

    // Assert: Ensure it returns 200 OK
    $response->assertOk();
});

it('denies non-admin users access to delete another user', function () {
    // Arrange: Create a user and another user
    $user = $this->createUser();
    $anotherUser = $this->createUser();

    // Act: Try to delete another user as a non-admin
    $response = $this->actingAs($anotherUser, 'sanctum')->deleteJson("/api/users/{$user->id}");

    // Assert: Ensure it returns 403 Forbidden
    $response->assertForbidden();
});

it('allows admin users to create a new user', function () {
    // Arrange: Create an admin user
    $admin = $this->createAdminUser();
    $userData = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // Act: Try to create a new user as an admin
    $response = $this->actingAs($admin, 'sanctum')->postJson('/api/users', $userData);

    // Assert: Ensure it returns 201 Created
    $response->assertCreated();
    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('denies non-admin users to create a new user', function () {
    // Arrange: Create a non-admin user
    $user = $this->createUser ();
    $userData = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // Act: Try to create a new user as a non-admin
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/users', $userData);

    // Assert: Ensure it returns 403 Forbidden
    $response->assertForbidden();
});

it('allows admin users to modify another users data', function () {
    // Arrange: Create a user and an admin user
    $user = $this->createUser ();
    $admin = $this->createAdminUser ();
    $updatedData = ['name' => 'Updated User'];

    // Act: Try to update another user's data as an admin
    $response = $this->actingAs($admin, 'sanctum')->patchJson("/api/users/{$user->id}", $updatedData);

    // Assert: Ensure it returns 200 OK
    $response->assertOk();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User']);
});

it('denies non-admin users to modify another users data', function () {
    // Arrange: Create a user and another user
    $user = $this->createUser ();
    $anotherUser  = $this->createUser ();
    $updatedData = ['name' => 'Updated User'];

    // Act: Try to update another user's data as a non-admin
    $response = $this->actingAs($anotherUser , 'sanctum')->patchJson("/api/users/{$user->id}", $updatedData);

    // Assert: Ensure it returns 403 Forbidden
    $response->assertForbidden();
});

it('allows admin users to delete a user', function () {
    // Arrange: Create a user and an admin user
    $user = $this->createUser();
    $admin = $this->createAdminUser();

    // Act: Try to delete another user as an admin
    $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/users/{$user->id}");

    // Assert: Ensure it returns 204 No Content
    $response->assertNoContent();
});

it('denies non-admin users from deleting another user', function () {
    // Arrange: Create a user and another user
    $user = $this->createUser (); // The user to be deleted
    $anotherUser  = $this->createUser (); // The non-admin user trying to delete

    // Act: Try to delete another user as a non-admin
    $response = $this->actingAs($anotherUser , 'sanctum')->deleteJson("/api/users/{$user->id}");

    // Assert: Ensure it returns 403 Forbidden
    $response->assertForbidden();
});
