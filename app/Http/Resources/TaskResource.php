<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'owner' => $user->can('viewAny', \App\Models\Task::class)
                ? ['id' => $this->user->id, 'name' => $this->user->name]
                : 'You',
        ];
    }
}
