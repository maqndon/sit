<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskDeadlineRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    use AuthorizesRequests;

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

        return response()->json(TaskResource::collection($tasks));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $task = $this->taskService->store($request);

        return response()->json(new TaskResource($task), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json(new TaskResource($task));  // Return the task as a resource
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);  // Check if the user is authorized

        $updatedTask = $this->taskService->update($request, $task);  // Use the service to update the task

        return response()->json(new TaskResource($updatedTask));  // Return the updated task as a resource
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);  // Check if the user is authorized

        $this->taskService->delete($task);  // Use the service to delete the task

        return response()->json(null, 204);  // Return a 204 status code with no content
    }

    /**
     * Show tasks by user.
     */
    public function showTasksByUser(User $user): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->getTasksByUser($user);

        return response()->json(TaskResource::collection($tasks));
    }

    /**
     * Show tasks by project.
     */
    public function showTasksByProject(Project $project): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->getTasksByProject($project);

        return response()->json(TaskResource::collection($tasks));
    }

    /**
     * Update task deadline.
     */
    public function updateDeadline(UpdateTaskDeadlineRequest $request, Task $task): JsonResponse
    {
        $this->authorize('updateDeadline', $task);

        $this->taskService->updateDeadline($request, $task);

        return response()->json(new TaskResource($task));
    }

    /**
     * Get overdue tasks.
     */
    public function getOverdueTasks(): JsonResponse
    {
        $tasks = Task::where('deadline', '<', now())->get();

        return response()->json(TaskResource::collection($tasks));
    }
}
