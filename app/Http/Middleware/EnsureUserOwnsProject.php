<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOwnsProject
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Skip validation for creating a new project or listing all projects
        if ($request->isMethod('post') || $request->routeIs('projects.index')) {
            return $next($request);
        }

        $project = $request->route('project');

        // Ensure we have a valid Task model
        if (! $project instanceof Project) {
            $project = Project::find($project);
        }

        if (! $project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // Allow access if the user is an admin or owns the project
        if ($user->isAdmin() || $project->user_id === $user->id) {
            return $next($request);
        }

        return $next($request);
    }
}
