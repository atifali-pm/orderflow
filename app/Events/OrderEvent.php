<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class OrderEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function eventName(): string
    {
        $short = class_basename(static::class);

        return strtolower(preg_replace('/(?<!^)([A-Z])/', '.$1', $short));
    }
}
