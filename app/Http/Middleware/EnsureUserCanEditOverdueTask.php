<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanEditOverdueTask
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $task = $request->route('task');

        if ($task && Carbon::parse($task->deadline)->isPast()) {
            if (auth()->user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized to edit overdue tasks'], 403);
            }
        }

        return $next($request);
    }
}
