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
            'project' => new ProjectSummaryResource($this->whenLoaded('project')),
            'description' => $this->description,
            'status' => $this->status,
            'deadline' => $this->deadline,
            'owner' => new UserSummaryResource($this->whenLoaded('user')),
        ];
    }
}
