<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyKey
{
    private const TTL_SECONDS = 86400;

    private const REDIS_PREFIX = 'orderflow:idempotency:';

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PATCH', 'PUT', 'DELETE'], true)) {
            return $next($request);
        }

        $key = trim((string) $request->header('Idempotency-Key'));

        if ($key === '') {
            return response()->json([
                'message' => 'Idempotency-Key header is required for this endpoint.',
            ], 400);
        }

        $redisKey = self::REDIS_PREFIX.$key;
        $cached = Redis::get($redisKey);

        if ($cached !== null) {
            $payload = json_decode($cached, true);

            return response()
                ->json($payload['body'], $payload['status'])
                ->header('X-Idempotency-Replay', 'true');
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() < 400) {
            $body = json_decode($response->getContent(), true);
            Redis::setex($redisKey, self::TTL_SECONDS, json_encode([
                'status' => $response->getStatusCode(),
                'body' => $body,
            ]));
        }

        return $response;
    }
}
