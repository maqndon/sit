<?php

use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\Traits\UserTrait;

uses(UserTrait::class);

beforeEach(function () {
    $this->refreshDatabase();
});

describe('Task Endpoints', function () {
    it('denies unauthenticated users access to the endpoint /api/tasks', function () {
        // Act: Try to access tasks without authentication
        $response = $this->getJson('/api/tasks');

        // Assert: Ensure it returns 401 Unauthorized
        $response->assertUnauthorized();
    });

    it('allows authenticated users to view their projects tasks', function () {
        // Arrange: Create a user and their tasks
        $user = $this->createUserWithProjectAndTasks();
        $project = Project::first();

        // Act & Assert: Authenticated user can see their tasks
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/projects/{$project->id}/tasks");

        $response->assertOk();
    });

    it('allows authenticated users to view their tasks', function () {
        // Arrange: Create a user and their tasks
        $user = $this->createUserWithTasks(5);

        // Act & Assert: Authenticated user can see their tasks
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

        $response
            ->assertOk()
            ->assertJsonCount(5);
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
            ->assertCreated()
            ->assertJsonFragment($taskData);

        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    });

    it('throws a 404 Status if the task does not exist', function () {
        // Arrange: Create an user without any task
        $user = $this->createUser();

        // Act: The user try to access a task that does not exist
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks/1');

        // Assert: Expect a 404 Not Found status
        $response->assertNotFound();
    });

    it('allows only numeric id', function () {
        // Arrange: Prepare the test
        // You can set up any necessary data or state here if needed

        // Act: Try to access the endpoint with a non-numeric ID
        $response = $this->get('/api/posts/abc');

        // Assert: Verify the response status is 404 Not Found
        $response->assertStatus(404);
    });
});

describe('Task Validation', function () {
    it('validates that status only allows specific values', function () {
        // Arrange: Create a user
        $user = $this->createUser();

        // Act: The authenticated user attempts to create a task with an invalid status
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
            'title' => 'Valid title',
            'description' => 'Task description',
            'status' => 'invalid_status',  // Invalid status
        ]);

        // Assert: Expect a 422 Unprocessable Entity status and a JSON structure for errors
        $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);
    });

    it('validates that title cannot exceed max length', function () {
        // Arrange: Create a user
        $user = $this->createUser();

        // Act: The authenticated user attempts to create a task with an overly long title
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
            'title' => str_repeat('A', 300),  // Exceeds max length
            'description' => 'Task description',
            'status' => 'todo',
        ]);

        // Assert: Expect a 422 Unprocessable Entity status and a JSON structure for errors
        $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);
    });

    it('validates that the title is required', function () {
        // Arrange: Create a user
        $user = $this->createUser();

        // Act: The authenticated user attempts to create a task without a title
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
            'title' => '',  // Empty title
            'description' => 'Valid description',
            'status' => 'todo',
        ]);

        // Assert: Ensure the response indicates a validation error for the title
        $response->assertStatus(422)->assertJsonStructure([
            'message',
            'errors' => ['title'],  // Validation error for the title
        ]);
    });

    it('validates that the description is required', function () {
        // Arrange: Create a user
        $user = $this->createUser();

        // Act: The authenticated user attempts to create a task without a description
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', [
            'title' => 'Valid title',
            'description' => '',  // Empty description
            'status' => 'todo',
        ]);

        // Assert: Ensure the response indicates a validation error for the description
        $response->assertStatus(422)->assertJsonStructure([
            'message',
            'errors' => ['description'],  // Validation error for the description
        ]);
    });

    it('validates that the deadline is in the future', function () {
        // Arrange: Create a user with tasks
        $user = $this->createUserWithTasks();
        $task = Task::first();

        // Act: Attempt to update the task with a past deadline
        $response = $this->actingAs($user, 'sanctum')->putJson("/api/tasks/{$task->id}", [
            'deadline' => now()->subDay()->toDateTimeString(),  // Past deadline
        ]);

        // Assert: Ensure validation errors are returned
        $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);
    });
});

describe('Task Management', function () {
    it('allows authenticated users to update their task', function () {
        // Arrange: Create a user and a task
        $user = $this->createUserWithTasks();
        $task = Task::first();  // Get the created task
        $updatedData = ['title' => 'Updated Task', 'description' => 'Updated description'];

        // Act: The authenticated user updates the task
        $response = $this->actingAs($user, 'sanctum')->putJson("/api/tasks/{$task->id}", $updatedData);

        // Assert: Ensure the task is updated successfully
        $response
            ->assertOk()
            ->assertJsonFragment($updatedData);
    });

    it('allows authenticated users to delete their task', function () {
        // Arrange: Create a user and a task
        $user = $this->createUserWithTasks();
        $task = Task::first();  // Get the created task

        // Act: The authenticated user deletes the task
        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/tasks/{$task->id}");

        // Assert: Ensure the task is deleted successfully
        $response->assertNoContent($status = 204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    });

    it('denies unauthorized user from deleting a task', function () {
        // Arrange: Create a user with a task and another user
        $user = $this->createUserWithTasks();
        $otherUser = $this->createUser();  // Another user
        $task = Task::first();  // Get the first task

        // Act: The unauthorized user attempts to delete the task
        $response = $this->actingAs($otherUser, 'sanctum')->deleteJson("/api/tasks/{$task->id}");

        // Assert: Expect a 403 Forbidden status
        $response->assertForbidden();
    });

    it('denies users to view tasks of another users project', function () {
        // Arrange: Create a user with a project and tasks
        $user = $this->createUserWithProjectAndTasks(3);
        $another_user = $this->createUser();
        $project = $user->projects()->first();  // Get the first project of the user

        // Act: The authenticated user requests the tasks of the project
        $response = $this->actingAs($another_user, 'sanctum')->getJson("/api/projects/{$project->id}/tasks");

        // Assert: Verify that the user can see the project's tasks
        $response->assertForbidden();
    });

    it('allows the task owner to update the task deadline', function () {
        // Arrange: Create a user with a task
        $user = $this->createUserWithTasks();
        $task = $user->tasks()->first();
        $updatedDeadline = ['deadline' => now()->addDay()->toDateTimeString()];

        // Act: The task owner updates the deadline
        $response = $this
            ->actingAs($user, 'sanctum')
            ->patchJson("/api/tasks/{$task->id}/deadline", $updatedDeadline);

        // Assert: Ensure the task is updated successfully
        $response
            ->assertOk()
            ->assertJsonFragment($updatedDeadline);

        // Verify in the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deadline' => $updatedDeadline['deadline'],
        ]);
    });

    it('allows users to see their overdue tasks', function () {
        // Arrange: Create a user and an overdue task for that user
        $user = $this->createUserWithOverdueTask();

        // Act: User tries to see their overdue tasks
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks/overdue');

        // Assert: User can see their overdue task
        $response->assertOk();
    });

    it('denies users to see another users overdue tasks', function () {
        // Arrange: Create a user and an overdue task for that user
        $user = $this->createUserWithOverdueTask();
        $user_without_task = $this->createUser();

        // Act: User tries to see their overdue tasks
        $response = $this->actingAs($user_without_task, 'sanctum')->getJson('/api/tasks/overdue');

        // Assert: User can access the endpoint but sees no tasks
        $response->assertOk();

        $content = $response->json();

        if (isset($content['data'])) {
            expect($content['data'])->toBeEmpty();
        } else {
            expect($content)->toBeEmpty();
        }
    });

    it('triggers the TaskUpdated event and executes the listener', function () {
        // Arrange: Prepare the test
        Log::shouldReceive('info')->once();  // Expect the Log::info method to be called once

        // Create a user and task
        $user = $this->createUserWithOverdueTask();
        $task = Task::first();
        $updatedData = ['title' => 'Updated Task Title'];

        // Fake the notification sending
        Notification::fake();

        // Act: Update the task
        $response = $this->actingAs($user, 'sanctum')->patchJson("/api/tasks/{$task->id}", $updatedData);

        // Assert: Verify the response is OK
        $response->assertOk();

        // Verify that the notification was sent
        Notification::assertSentTo($user, TaskOverdueNotification::class);
    });
});

describe('Admin Privileges', function () {
    it('allows admin users to view all tasks', function () {
        // Arrange: Create an admin user and some tasks for different users
        $adminUser = $this->createAdminUser();
        $user = $this->createUserWithTasks();  // Create a regular user with a task
        $otherTask = Task::factory()->for($this->user)->create();  // Create another task for the same user

        // Act: The admin user retrieves all tasks
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/tasks');

        // Assert: Ensure the response is successful and contains the tasks
        $response
            ->assertOk()
            ->assertJsonCount(2);  // Assuming there are 2 tasks in total
    });

    it('allows admin users to update any task', function () {
        // Arrange: Create an admin user and a task for a regular user
        $adminUser = $this->createAdminUser();
        $user = $this->createUserWithTasks();  // Create a regular user with a task
        $task = Task::first();  // Get the created task
        $updatedData = ['title' => 'Admin Updated Task', 'description' => 'Updated by admin'];

        // Act: The admin user updates the task
        $response = $this->actingAs($adminUser, 'sanctum')->putJson("/api/tasks/{$task->id}", $updatedData);

        // Assert: Ensure the task is updated successfully
        $response
            ->assertOk()
            ->assertJsonFragment($updatedData);
    });

    it('allows admin users to delete any task', function () {
        // Arrange: Create an admin user and a task for a regular user
        $adminUser = $this->createAdminUser();
        $user = $this->createUserWithTasks();  // Create a regular user with a task
        $task = Task::first();  // Get the created task

        // Act: The admin user deletes the task
        $response = $this->actingAs($adminUser, 'sanctum')->deleteJson("/api/tasks/{$task->id}");

        // Assert: Ensure the task is deleted successfully
        $response->assertNoContent($status = 204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    });
});
