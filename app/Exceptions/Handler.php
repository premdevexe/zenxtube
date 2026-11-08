<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            $status = 500;

            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'code' => 422,
                    'message' => 'Validation failed.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
                $exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'The requested API resource or endpoint was not found.',
                ], 404);
            }

            if (method_exists($exception, 'getStatusCode')) {
                $status = $exception->getStatusCode();
            }

            $message = config('app.debug')
                ? $exception->getMessage()
                : ($status === 500 ? 'An unexpected server error occurred.' : $exception->getMessage());

            return response()->json([
                'success' => false,
                'code' => $status,
                'message' => $message ?: 'An error occurred.',
            ], $status);
        }

        return parent::render($request, $exception);
    }
}
