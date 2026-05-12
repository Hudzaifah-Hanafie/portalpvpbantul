<?php

namespace App\Listeners;

use App\Events\GamificationPointEarned;
use App\Models\GamificationPoint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AwardGamificationPoint implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The time (seconds) before the job should be processed.
     * We don't necessarily need a delay, but this makes it non-blocking.
     */
    public $delay = 0;

    /**
     * Handle the event.
     */
    public function handle(GamificationPointEarned $event): void
    {
        GamificationPoint::create([
            'user_id' => $event->userId,
            'points' => $event->points,
            'source_type' => $event->sourceType,
            'source_id' => $event->sourceId,
            'description' => $event->description,
        ]);
    }
}
