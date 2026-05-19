<?php

namespace App\Support\N8n;

class HmacSigner
{
    public function __construct(private string $secret)
    {
    }

    public function canonicalize(array $payload): string
    {
        return json_encode(
            self::sortKeysDeep($payload),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    public function sign(array $payload): string
    {
        return hash_hmac('sha256', $this->canonicalize($payload), $this->secret);
    }

    public function verify(string $body, string $signature): bool
    {
        $expected = hash_hmac('sha256', $body, $this->secret);

        return hash_equals($expected, $signature);
    }

    private static function sortKeysDeep(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => self::sortKeysDeep($item), $value);
        }

        ksort($value);

        return array_map(fn ($item) => self::sortKeysDeep($item), $value);
    }
}
