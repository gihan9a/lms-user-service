<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $errors = [];
        $code = 500;
        if ($exception instanceof HttpResponseException) {
            $errors['error'] = [$exception->getResponse()];
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        } elseif ($exception instanceof ModelNotFoundException) {
            $errors['error'] = [$exception->getMessage()];
            $code = Response::HTTP_NOT_FOUND;
        } elseif ($exception instanceof AuthorizationException) {
            $errors['error'] = [$exception->getMessage()];
            $code = Response::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof ValidationException && $exception->getResponse()) {
            $errors = $exception->errors();
            $code = Response::HTTP_BAD_REQUEST;
        }

        return response()->json([
            'code' => $code,
            'data' => null,
            'errors' => $errors
        ], $code);
    }
}
