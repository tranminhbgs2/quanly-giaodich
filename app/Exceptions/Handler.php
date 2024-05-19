<?php

namespace App\Exceptions;

use App\Models\LogAuth;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
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
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);

        if ($exception instanceof UnauthorizedHttpException ) {
            return response()->json([
                'code' => 401,
                'error' => 'Bạn vui lòng, đăng nhập',
                'data' => null,
            ]);
        }

        if ($exception->getCode() === 2002 || $exception instanceof QueryException) {
            return response()->json([
                'code' => 500,
                'error' => 'Bạn vui lòng, kiểm tra kết nối CSDL',
                'data' => null,
            ]);
        }

    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
    	if ($exception instanceof UnauthorizedHttpException ) {
            // Lấy mã phiên làm việc qua header hoặc truyền tham số lên nếu có
    	    if ($request->has('session_id')) {
                $session_id = request('session_id', null);
            } else {
                $session_id = $request->header('session_id');
            }

            // Cập nhật thời gian logout nếu có
            if ($session_id) {
                LogAuth::where('session_id', $session_id)->update([
                    'logged_out_at' => Carbon::now()
                ]);
            }

    	    return response()->json([
                'code' => 401,
                'error' => 'Bạn vui lòng, đăng nhập',
                'data' => null,
            ]);
        }

        if ($exception instanceof QueryException) {
            return response()->json([
                'code' => 400,
                'error' => 'Hệ thống đang bận, Bạn vui lòng thử lại sau',
                'message_dev' => $exception->getMessage(),
                'data' => null,
            ]);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'code' => 404,
                'error' => 'Sai đường dẫn api.',
                'data' => null,
            ]);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'code' => '405',
                'error' => 'Sai phương thức gọi api.',
                'data' => null,
            ]);
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // if ($request->expectsJson()) {
        //     return response()->json(['error' => 'Unauthenticated.'], 401);
        // }

        // return redirect()->guest(route('login'));

        if (strpos($request->getUri(), '/api') > 0) {
            return response()->json([
                'code' => 401,
                'error' => 'Bạn vui lòng, đăng nhập.',
                'data' => null,
            ]);
        }
    }
}
