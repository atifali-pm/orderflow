<?php

namespace App\Support\N8n;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;

class WebhookDispatcher
{
    public function __construct(
        private HttpFactory $http,
        private HmacSigner $signer,
        private string $baseUrl,
        private int $timeout = 10,
    ) {
    }

    public function send(string $event, array $payload): Response
    {
        $body = $this->signer->canonicalize($payload);
        $signature = $this->signer->sign($payload);

        return $this->http
            ->withHeaders([
                'X-Orderflow-Event' => $event,
                'X-Orderflow-Signature' => $signature,
            ])
            ->timeout($this->timeout)
            ->withBody($body, 'application/json')
            ->post($this->buildUrl($event));
    }

    private function buildUrl(string $event): string
    {
        $slug = str_replace('.', '-', $event);

        return rtrim($this->baseUrl, '/').'/'.$slug;
    }
}
