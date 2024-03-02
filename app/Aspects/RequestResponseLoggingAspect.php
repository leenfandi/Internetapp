<?php

namespace App\Aspects;

use App\Services\LoggingServices;
use Illuminate\Support\Facades\Log;
use Closure as MiddlewareClosure;
class RequestResponseLoggingAspect
{
    protected LoggingServices $loggingService;

    public function __construct(LoggingServices $loggingService = null)
    {
        $this->loggingService = $loggingService ?? new LoggingServices();
    }

    public function __invoke($request, MiddlewareClosure $next)
    {
        $controller = $request->route()->getAction('controller');
        $method = $request->route()->getActionMethod();

        $this->before($request, $controller, $method);

        $response = $next($request);

        $this->after($request, $controller, $method, $response);

        return $response;
    }

    public function before($request, $controller, $method)
    {
        $this->loggingService->logRequest($request, $controller, $method);
    }

    public function after($request, $controller, $method, $response)
    {
        $this->loggingService->logResponse($response, $controller, $method);
    }

    public function exception($request, $controller, $method, $exception)
    {
        $this->loggingService->logException($exception, $controller, $method);
    }
}
