<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskOverdueNotification;
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

        // Check if the task's deadline has passed and the status is not 'done'
        if ($event->task->deadline < now() && $event->task->status !== 'done') {
            // Send the notification to the user who owns the task
            $user = $event->task->user;
            $user->notify(new TaskOverdueNotification($event->task));
            Log::info('Checking task deadline', [
                'task_id' => $event->task->id,
                'user_id' => $event->task->user->id,
                'status' => $event->task->status,
                'deadline' => $event->task->deadline,
            ]);
        }
    }
}
