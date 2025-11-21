<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Responses\ApiResponse;
use Throwable;

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

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions.
     */
    protected function handleApiException($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return ApiResponse::error(
                'Validation failed',
                422,
                $exception->errors()
            );
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        if ($exception instanceof NotFoundHttpException) {
            return ApiResponse::error('Resource not found', 404);
        }

        if ($exception instanceof HttpException) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->getStatusCode()
            );
        }

        $statusCode = 500;
        $message = 'Internal server error';

        if (config('app.debug')) {
            $message = $exception->getMessage();
        }

        return ApiResponse::error($message, $statusCode);
    }
}

