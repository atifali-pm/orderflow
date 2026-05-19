<?php

namespace App\Listeners;

use App\Events\OrderEvent;
use App\Jobs\SyncToN8nJob;

class QueueN8nSync
{
    public function handle(OrderEvent $event): void
    {
        SyncToN8nJob::dispatch($event->eventName(), $event->order->id);
    }
}
