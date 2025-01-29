<?php

namespace App\Services;

use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    /**
     * Store a new task.
     *
     * @param StoreTaskRequest $request
     * @return Task
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
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasks()
    {
        return Task::where('user_id', Auth::id())->get();
    }

    /**
     * Get a specific task by ID.
     *
     * @param int $id
     * @return Task
     */
    public function getTaskById($id): Task
    {
        return Task::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Update a task.
     *
     * @param Task $task
     * @param UpdateTaskRequest $request
     * @return Task
     */
    public function update(UpdateTaskRequest $request, Task $task): Task
    {
        $task->update($request->validated());  // Update the task with validated data
        return $task;
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return void
     */
    public function delete(Task $task): void
    {
        $task->delete();
    }

    /**
     * Authorize the user for the given task.
     *
     * @param Task $task
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeTask(Task $task): void
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');  // Check if the user is the owner
        }
    }
}
