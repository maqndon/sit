<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use Illuminate\Support\Facades\Log;

class CheckTaskDeadline
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskUpdated $event): void
    {
        Log::info('Checking task deadline', [
            'task_id' => $event->task->id,
            'deadline' => $event->task->deadline,
        ]);
    }
}
