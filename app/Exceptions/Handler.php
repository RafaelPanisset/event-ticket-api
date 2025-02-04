<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'error'    => 'Validation Error',
                    'messages' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'error'   => 'Resource Not Found',
                    'message' => 'The requested resource could not be found.',
                ], 404);
            }

            if ($exception instanceof BadRequestHttpException) {
                return response()->json([
                    'error'   => 'Bad Request',
                    'message' => $exception->getMessage() ?: 'Bad request parameters.',
                ], 400);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'error'   => 'Not Found',
                    'message' => 'The requested endpoint does not exist.',
                ], 404);
            }

            if ($exception instanceof NotEnoughTicketsException ||
            $exception instanceof PastEventReservationException ||
            $exception instanceof ReservationAlreadyExistsException ||
            $exception instanceof ReservationNotFoundException) 
            {
                return response()->json([
                    'error'   => class_basename($exception), 
                    'message' => $exception->getMessage(),
                ], $exception->getCode() ?: 400);
            }

            return response()->json([
                'error'   => 'Server Error',
                'message' => $exception->getMessage(),
            ], method_exists($exception, 'getCode') && $exception->getCode() >= 400
                ? $exception->getCode()
                : 500);
        }

        return parent::render($request, $exception);
    }
}
