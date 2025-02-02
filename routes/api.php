<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/unauthenticated', [AuthController::class, 'unauthenticated'])->name('login');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('tasks', TaskController::class);

    Route::apiResource('projects', ProjectController::class);

    // Show tasks by user
    Route::get('users/{user}/tasks', [TaskController::class, 'showTasksByUser'])
        ->where('user', '[0-9]+');

    // Show tasks by project
    Route::get('projects/{project}/tasks', [TaskController::class, 'showTasksByProject'])
        ->where('project', '[0-9]+');

    // Show overdue tasks
    Route::get('tasks/overdue', [TaskController::class, 'showOverdueTasks'])
        ->middleware(EnsureUserCanEditOverdueTask::class);

    // Update only the deadline of a task
    Route::patch('tasks/{task}/deadline', [TaskController::class, 'updateDeadline']);
});
