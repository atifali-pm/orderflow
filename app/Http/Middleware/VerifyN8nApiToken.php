<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyN8nApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('n8n.api_token');
        $provided = (string) $request->bearerToken();

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Invalid n8n API token.'], 401);
        }

        return $next($request);
    }
}
