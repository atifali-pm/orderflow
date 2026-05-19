<?php

use App\Support\N8n\HmacSigner;
use App\Support\N8n\WebhookDispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

it('posts canonical body with signature and event headers', function () {
    Http::fake([
        '*' => Http::response(['ok' => true], 200),
    ]);

    $signer = new HmacSigner('top-secret');
    $dispatcher = new WebhookDispatcher(app(HttpFactory::class), $signer, 'http://n8n.test/webhook');

    $payload = ['z' => 1, 'a' => ['b' => 2, 'a' => 1]];
    $response = $dispatcher->send('order.placed', $payload);

    expect($response->successful())->toBeTrue();

    Http::assertSent(function ($request) use ($signer, $payload) {
        $expectedBody = $signer->canonicalize($payload);
        $expectedSig = $signer->sign($payload);

        return $request->url() === 'http://n8n.test/webhook/order-placed'
            && $request->body() === $expectedBody
            && $request->header('X-Orderflow-Event')[0] === 'order.placed'
            && $request->header('X-Orderflow-Signature')[0] === $expectedSig;
    });
});
