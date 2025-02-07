<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class EnsureUserOwnsTask
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Skip validation for creating a new task or listing all tasks
        if ($request->isMethod('post') || $request->routeIs('tasks.index')) {
            return $next($request);
        }

        $task = $request->route('task');

        // Ensure we have a valid Task model
        if (!$task instanceof Task) {
            $task = Task::find($task);
        }

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        // Allow access if the user is an admin or owns the task
        if ($user->isAdmin() || $task->user_id === $user->id) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
