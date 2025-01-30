<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $tasks = $this->taskService->getTasks();  // Get tasks for the authenticated user

        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTaskRequest  $request
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->store($request);

        return response()->json($task, 201);  // Return the created task with a 201 status code
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        $this->taskService->authorizeTask($task);  // Check if the user is the owner

        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UpdateTaskRequest  $request
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->taskService->authorizeTask($task);  // Check if the user is the owner
        $updatedTask = $this->taskService->update($request, $task);  // Use the service to update the task

        return response()->json($updatedTask);  // Return the updated task
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->authorizeTask($task);  // Check if the user is the owner
        $this->taskService->delete($task);  // Use the service to delete the task

        return response()->json(null, 204);  // Return a 204 status code with no content
    }
}
