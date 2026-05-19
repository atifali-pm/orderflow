<?php

namespace App\Providers;

use App\Events\OrderCancelled;
use App\Events\OrderPaid;
use App\Events\OrderPlaced;
use App\Events\OrderShipped;
use App\Listeners\QueueN8nSync;
use App\Support\N8n\HmacSigner;
use App\Support\N8n\WebhookDispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HmacSigner::class, function ($app) {
            return new HmacSigner((string) config('n8n.hmac_secret'));
        });

        $this->app->singleton(WebhookDispatcher::class, function ($app) {
            return new WebhookDispatcher(
                $app->make(HttpFactory::class),
                $app->make(HmacSigner::class),
                (string) config('n8n.webhook_url_base'),
                (int) config('n8n.timeout', 10),
            );
        });
    }

    public function boot(): void
    {
        foreach ([OrderPlaced::class, OrderPaid::class, OrderShipped::class, OrderCancelled::class] as $event) {
            Event::listen($event, [QueueN8nSync::class, 'handle']);
        }
    }
}
