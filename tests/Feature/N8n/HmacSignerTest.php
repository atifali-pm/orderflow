<?php

use App\Support\N8n\HmacSigner;

it('canonicalizes payloads with deeply sorted keys', function () {
    $signer = new HmacSigner('secret');

    $a = ['z' => 1, 'a' => ['nested_b' => 2, 'nested_a' => 1]];
    $b = ['a' => ['nested_a' => 1, 'nested_b' => 2], 'z' => 1];

    expect($signer->canonicalize($a))->toBe($signer->canonicalize($b));
});

it('preserves list order while sorting object keys', function () {
    $signer = new HmacSigner('secret');

    $payload = [
        'items' => [
            ['sku' => 'B', 'qty' => 2],
            ['sku' => 'A', 'qty' => 1],
        ],
    ];

    $canonical = $signer->canonicalize($payload);

    expect($canonical)->toBe('{"items":[{"qty":2,"sku":"B"},{"qty":1,"sku":"A"}]}');
});

it('produces stable signatures regardless of input key order', function () {
    $signer = new HmacSigner('top-secret');

    $sigA = $signer->sign(['b' => 2, 'a' => 1]);
    $sigB = $signer->sign(['a' => 1, 'b' => 2]);

    expect($sigA)->toBe($sigB)
        ->and(strlen($sigA))->toBe(64);
});

it('verifies a signature against a known canonical body', function () {
    $signer = new HmacSigner('top-secret');
    $body = '{"hello":"world"}';
    $sig = hash_hmac('sha256', $body, 'top-secret');

    expect($signer->verify($body, $sig))->toBeTrue()
        ->and($signer->verify($body, 'nope'))->toBeFalse();
});
