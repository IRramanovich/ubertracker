<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\DriversLastStatus;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Class DriversStatusChange
 * @package App\Events
 */
class DriversStatusChange implements ShouldBroadcastNow
{
    use SerializesModels;

    public $driverStatus;

    /**
     * Create a new event instance.
     *
     * @param DriversLastStatus $driverStatus
     */
    public function __construct( DriversLastStatus $driverStatus )
    {
        $this->driverStatus = $driverStatus->toArray();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return ['drivers-status'];
    }
}
