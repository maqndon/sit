<?php

namespace App\Services;

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserService
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
     * Store a new task.
     */
    public function store(StoreUserRequest $request): User
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
    }

    /**
     * Get all tasks for the authenticated user.
     */
    public function getUsers(): ResourceCollection
    {
        // Check if the authenticated user can view any users
        if ($this->user->can('viewAny', User::class)) {
            $users = User::all();
        } else {
            // If they cannot, return only the authenticated user's data
            $users = User::where('id', $this->user->id)->get();
        }

        return UserResource::collection($users);
    }

    /**
     * Get a specific task by ID.
     */
    public function getUserById(User $user): UserResource
    {
        return new UserResource($user);  // Return the task as a resource
    }

    /**
     * Update a task.
     */
    public function update(UpdateUserRequest $request, User $user): User
    {
        $user->update($request->validated());  // Update the task with validated data

        return $user;
    }

    /**
     * Delete a task.
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
