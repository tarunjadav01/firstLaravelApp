<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;

class ProcessEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;
    public $event;

    public function __construct(array $event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        $hash = md5(
            $this->event['tenant_id'] .
            $this->event['session_id'] .
            $this->event['event_type'] .
            $this->event['timestamp']
        );
        
        // Idempotency check
        if (Event::where('event_hash', $hash)->exists()) {
            return;
        }

        // Session handling
        $session = EventSession::firstOrCreate(
            [
                'tenant_id' => $this->event['tenant_id'],
                'session_id' => $this->event['session_id'],
            ]);

        $eventTime = Carbon::parse($this->event['timestamp']);
        // Store event
        Event::create([
            'tenant_id'       => $this->event['tenant_id'],
            'session_id'      => $this->event['session_id'],
            'event_type'      => $this->event['event_type'],
            'event_timestamp' => $eventTime,
            'event_hash'      => $hash,
        ]);
    }
}
