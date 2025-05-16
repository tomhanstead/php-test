<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        // Add context that will be included with all log entries for this request
        Log::withContext([
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Log the request with payload data
        Log::info('API Request received', [
            'payload' => $this->sanitizePayload($request->all()),
        ]);

        // Process the request
        $response = $next($request);

        // Log the response
        Log::info('API Response sent', [
            'status' => $response->getStatusCode(),
            'duration_ms' => defined('LARAVEL_START') ? round((microtime(true) - LARAVEL_START) * 1000) : null,
        ]);

        // Add the request ID to the response headers
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    /**
     * Sanitize the payload to remove all (possible sensitive information)
     */
    protected function sanitizePayload(array $payload): array
    {
        // keys that I will filter out
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'api_key'];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
            } elseif (is_string($value) && in_array(strtolower($key), $sensitiveKeys)) {
                $payload[$key] = '[REDACTED]';
            }
        }

        return $payload;
    }
}
