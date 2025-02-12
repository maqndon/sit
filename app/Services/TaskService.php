<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskDeadlineRequest;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TaskService
{
    protected User $user;

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
        $tasks = Task::with(['project', 'user'])
            ->when($this->user->cannot('viewAny', Task::class), function (Builder $query):Builder{
                return $query->where('user_id', $this->user->id);
            })
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Get all tasks for the authenticated user.
     */
    public function getOverdueTasks(): ResourceCollection
    {
        $tasks = Task::with(['user', 'project'])
            ->where('deadline', '<', now())
            ->where('status', '!=', 'done')
            ->when($this->user->cannot('viewAny', Task::class), function (Builder $query):Builder {
                return $query->where('user_id', $this->user->id);
            })
            ->orderBy('deadline', 'asc')
            ->paginate(50);

        return TaskResource::collection($tasks);
    }

    /**
     * Get a specific task by ID.
     */
    public function getTaskById(Task $task): TaskResource
    {
        return new TaskResource($task);  // Return the task as a resource
    }

    /**
     * Get a specific task by given User.
     */
    public function getTasksByUser(User $user): ResourceCollection
    {
        $tasks = Task::with('user')
            ->where('user_id', $user->id)
            ->with('project')
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Get a specific task by given Project.
     */
    public function getTasksByProject(Project $project): ResourceCollection
    {
        $tasks = Task::with('user')
            ->where('project_id', $project->id)
            ->with('project')
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): Task
    {
        $task->update($request->validated());  // Update the task with validated data

        return $task;
    }

    /**
     * Update the deadline of a task.
     */
    public function updateDeadline(UpdateTaskDeadlineRequest $request, Task $task): Task
    {
        $task->update($request->validated());

        return $task;
    }

    /**
     * Delete a task.
     */
    public function delete(Task $task): void
    {
        $task->delete();
    }
}
