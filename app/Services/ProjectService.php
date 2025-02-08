<?php

namespace App\Services;

use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProjectService
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
     * Store a new Project.
     */
    public function store(StoreProjectRequest $request): Project
    {
        return Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Get all Projects for the authenticated user.
     */
    public function getProjects(): ResourceCollection
    {
        $projects = Project::with('user')
            ->when($this->user->cannot('viewAny', Project::class), function ($query) {
                return $query->where('user_id', $this->user->id);
            })
            ->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Get a specific Project by ID.
     */
    public function getProjectById(Project $project)
    {
        return new ProjectResource($project);  // Return the Project as a resource
    }

    /**
     * Update a Project.
     */
    public function update(UpdateProjectRequest $request, Project $project): Project
    {
        $project->update($request->validated());  // Update the Project with validated data

        return $project;
    }

    /**
     * Delete a project.
     */
    public function delete(Project $project): void
    {
        $project->delete();
    }
}
