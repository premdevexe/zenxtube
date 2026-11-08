<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseApiController extends Controller
{
    protected $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Compute elapsed execution time in milliseconds.
     */
    protected function getExecutionTimeMs()
    {
        if (!$this->startTime) {
            return 0;
        }
        return round((microtime(true) - $this->startTime) * 1000, 2);
    }

    /**
     * Build standard success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @param array $extraMeta
     * @return JsonResponse
     */
    protected function successResponse($data = null, $message = 'Operation successful.', $code = 200, array $extraMeta = [])
    {
        $meta = array_merge([
            'timestamp' => now()->toIso8601String(),
            'api_version' => 'v1',
            'execution_time_ms' => $this->getExecutionTimeMs(),
        ], $extraMeta);

        $payload = [
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ];

        return response()->json($payload, $code);
    }

    /**
     * Build standard error JSON response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @param array $extraMeta
     * @return JsonResponse
     */
    protected function errorResponse($message = 'An error occurred.', $code = 400, $errors = null, array $extraMeta = [])
    {
        $meta = array_merge([
            'timestamp' => now()->toIso8601String(),
            'api_version' => 'v1',
            'execution_time_ms' => $this->getExecutionTimeMs(),
        ], $extraMeta);

        $payload = [
            'success' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
            'meta' => $meta,
        ];

        return response()->json($payload, $code);
    }

    /**
     * Retrieve the authenticated ApiDevice from the request attributes.
     *
     * @param Request|null $request
     * @return \App\ApiDevice|null
     */
    protected function currentDevice(Request $request = null)
    {
        $req = $request ?: request();
        return $req->attributes->get('api_device');
    }

    /**
     * Retrieve the verified ApiClient from the request attributes.
     *
     * @param Request|null $request
     * @return \App\ApiClient|null
     */
    protected function currentClient(Request $request = null)
    {
        $req = $request ?: request();
        return $req->attributes->get('api_client');
    }
}
