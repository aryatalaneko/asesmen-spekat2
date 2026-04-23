<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public array  $payload;
    public int    $scheduleId;

    public function __construct(int $scheduleId, string $action, array $payload = [])
    {
        $this->scheduleId = $scheduleId;
        $this->action     = $action;
        $this->payload    = $payload;
    }

    public function broadcastOn(): array
    {
        return [new Channel('exam.' . $this->scheduleId)];
    }

    public function broadcastAs(): string
    {
        return 'ExamStateChanged';
    }

    /**
     * Data yang dikirim ke client JavaScript via WebSocket.
     */
    public function broadcastWith(): array
    {
        return [
            'action'      => $this->action,
            'schedule_id' => $this->scheduleId,
            'payload'     => $this->payload,
        ];
    }
}
