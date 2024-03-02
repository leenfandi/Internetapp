<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoggingServices
{
    public function logRequest($request, $controller, $method)
    {
        $controllerName = is_object($controller) ? get_class($controller) : $controller;

        Log::channel('request_response')->info("Received Request: " . json_encode([
            'url' => $request->fullUrl(),
            'controller' => $controllerName,
            'method' => $method,
            'parameters' => $request->all(),
        ]));
    }

    public function logResponse($response, $controller, $method)
    {
        $controllerName = is_object($controller) ? get_class($controller) : $controller;

        Log::channel('request_response')->info("Sent Response from $controllerName::$method: " . json_encode([
            'content' => $response->getContent(),
            'status_code' => $response->getStatusCode(),
        ]));
    }

    public function logException($exception, $controller, $method)
    {
        $controllerName = is_object($controller) ? get_class($controller) : $controller;

        Log::channel('request_response')->error("Exception in $controllerName::$method: " . $exception->getMessage());
    }
}
