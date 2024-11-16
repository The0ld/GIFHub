<?php

namespace App\Http\Middleware;

use App\Models\ServiceLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogServiceInteraction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Capture the init Request
        $startTime = microtime(true);

        // Process the Request and get the Response
        $response = $next($request);

        // Sanitize the request body
        $requestData = json_decode($request->getContent(), true);
        if (isset($requestData['password'])) {
            unset($requestData['password']);
        }

        $responseData = json_decode($response->getContent(), true);
        if (isset($responseData['data']['access_token'])) {
            unset($responseData['data']['access_token']);
        }

        // Capture required data to logs
        $logData = [
            'user_id' => Auth::id(),
            'service' => $request->path(),
            'request_body' =>  $request->method() === 'GET' ? $request->query() : $requestData,
            'response_status' => $response->getStatusCode(),
            'response_body' => $responseData,
            'ip_address' => $request->ip(),
            'duration' => round((microtime(true) - $startTime) * 1000) . 'ms',
        ];

        Log::info('Info: ' . json_encode($logData));

        $this->storageLogs($logData);

        return $response;
    }

    private function storageLogs(array $logData)
    {
        ServiceLog::create($logData);
    }
}
