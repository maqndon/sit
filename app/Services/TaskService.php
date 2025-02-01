<?php

namespace App\Services;

use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TaskService
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();

        if (! $this->user) {
            throw new HttpException(401, 'Unauthenticated.');
        }
    }

    /**
     * Store a new task.
     */
    public function store(StoreTaskRequest $request): Task
    {
        return Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => Auth::id(),  // Associate the task with the authenticated user
        ]);
    }

    /**
     * Get all tasks for the authenticated user.
     */
    public function getTasks(): ResourceCollection
    {
        $tasks = Task::with('user')
            ->when($this->user->cannot('viewAny', Task::class), function ($query) {
                return $query->where('user_id', $this->user->id);
            })
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Get a specific task by ID.
     */
    public function getTaskById(Task $task)
    {
        $this->authorizeTask($task);  // Check authorization

        return new TaskResource($task);  // Return the task as a resource
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): Task
    {
        $this->authorizeTask($task);  // Check authorization

        $task->update($request->validated());  // Update the task with validated data

        return $task;
    }

    /**
     * Delete a task.
     */
    public function delete(Task $task): void
    {
        $this->authorizeTask($task);  // Check authorization

        $task->delete();
    }

    /**
     * Authorize the user for the given task.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeTask(Task $task): void
    {
        // Allow admin users to update any task
        if ($this->user->role === 'admin') {
            return;  // Admin can proceed
        }

        // Check if the user is the owner of the task
        if ($task->user_id !== $this->user->id) {
            throw new HttpException(403, 'Unauthorized');
        }
    }
}
