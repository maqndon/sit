<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::where('user_id', Auth::id())->get(); // Get tasks for the authenticated user
        return response()->json([
            $tasks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param \App\Http\Requests\StoreTaskRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => Auth::id(), // Associate the task with the authenticated user
        ]);

        return response()->json($task, 201); // Return the created task with a 201 status code
    }

    /**
     * Display the specified resource.
     * 
     * @param  \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403); // Check if the user is the owner
        }

        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  App\Http\Requests\UpdateTaskRequest $request
     * @param  \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403); // Check if the user is the owner
        }

        $task->update($request->only(['title', 'description', 'status']));

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  \App\Models\Task $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403); // Check if the user is the owner
        }

        $task->delete();

        return response()->json(null, 204); // Return a 204 status code with no content
    }
}
