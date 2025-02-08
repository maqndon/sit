<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/unauthenticated', [AuthController::class, 'unauthenticated'])->name('login');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);

    // Show overdue tasks
    Route::get('tasks/overdue', [TaskController::class, 'showOverdueTasks']);

    Route::apiResource('tasks', TaskController::class)
        ->middleware('task_owner');

    Route::apiResource('projects', ProjectController::class)
        ->middleware('project_owner');

    // Show tasks by user
    Route::get('users/{user}/tasks', [TaskController::class, 'showTasksByUser'])
        ->where('user', '[0-9]+');

    // Show tasks by project
    Route::get('projects/{project}/tasks', [TaskController::class, 'showTasksByProject'])
        ->where('project', '[0-9]+');

    // Update only the deadline of a task
    Route::patch('tasks/{task}/deadline', [TaskController::class, 'updateDeadline']);
});
