<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GamificationPointEarned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $userId;
    public int $points;
    public string $sourceType;
    public string $sourceId;
    public string $description;

    /**
     * Create a new event instance.
     */
    public function __construct(string $userId, int $points, string $sourceType, string $sourceId, string $description)
    {
        $this->userId = $userId;
        $this->points = $points;
        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
        $this->description = $description;
    }
}
