<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // 🛡️ Standardize all API Error Responses
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return $this->handleApiExceptions($e);
            }
        });
    }

    private function handleApiExceptions(Throwable $e)
    {
        $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $requestId = request()->attributes->get('request_id');

        $response = [
            'success' => false,
            'message' => 'Server Error',
            'data' => null,
            'request_id' => $requestId,
        ];

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $response['message'] = 'Validation Error';
            $response['data'] = $e->errors();
            $code = 422;
        } elseif ($e instanceof \App\Exceptions\CouponValidationException) {
            $response['message'] = $e->getMessage() ?: 'Validation Error';
            $code = 422;
        } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $response['message'] = 'Resource not found';
            $code = 404;
        } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
            $response['message'] = 'Unauthenticated';
            $code = 401;
        } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $response['message'] = 'Route not found';
            $code = 404;
        } else {
            $response['message'] = $e->getMessage() ?: 'Server Error';
            if (config('app.debug')) {
                $response['data'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => array_slice($e->getTrace(), 0, 5)
                ];
            }
        }

        return response()->json($response, $code);
    }
}
