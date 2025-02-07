<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->getUsers();  // Get all users

        return response()->json(UserResource::collection($users));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->store($request);

        return response()->json(new UserResource($user), 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json(new UserResource($user));  // Return the user as a resource
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);  // Check if the user is authorized
        $updatedUser = $this->userService->update($request, $user);  // Use the service to update the user

        return response()->json(new UserResource($updatedUser));  // Return the updated user as a resource
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);  // Check if the user is authorized
        $this->userService->delete($user);  // Use the service to delete the user

        return response()->json(null, 204);  // Return a 204 status code with no content
    }
}
