<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    // public function render($request, Throwable $e)
    // {
    //     // Extract data set by your middleware
    //     $logData = $request->attributes->get('log_data', []);

    //     // Log error with full context
    //     \Log::error('System Error', [
    //         'message' => $e->getMessage(),
    //         'exception' => get_class($e),
    //         'file' => $e->getFile(),
    //         'line' => $e->getLine(),
    //         'trace' => $e->getTraceAsString(),
    //         'request' => $logData,
    //     ]);

    //     return parent::render($request, $e);
    // }

    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            $status = 500;

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();
            }

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], $status);
        }

        if ($exception instanceof ValidationException) {
            return response()->view('errors.general', [
                'status' => 422,
            ], 422);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->view('errors.general', [
                'status' => 401,
            ], 401);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->view('errors.general', [
                'status' => 403,
            ], 403);
        }

        $status = 500;

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
        }

        return response()->view('errors.general', [
            'status' => $status,
        ], $status);
    }

    public function report(Throwable $e)
    {
        // Get data from middleware
        $logData = request()->attributes->get('log_data', []);

        // Only log the error message
        $message = $e->getMessage();

        // Build a simple log array (no trace)
        $log = [
            'message' => $message,
            'info' => $logData,
        ];

        // Log to custom channel
        \Log::channel('custom')->error(json_encode($log));

        parent::report($e);
    }



}
