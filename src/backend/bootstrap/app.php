<?php

use App\Http\Middleware\CacheResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Modules\Identity\Infrastructure\Http\Middleware\PermissionMiddleware;
use App\Modules\Shared\Exceptions\AppException;
use App\Modules\Shared\Exceptions\ValidationException as SharedValidationException;
use App\Modules\Shared\Http\Middleware\ForceJsonMiddleware;
use App\Modules\Shared\Http\Resources\ErrorResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonMiddleware::class,
        ]);

        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'response_cache' => CacheResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Domain AppException — each class knows its own status + error code
        $exceptions->render(function (AppException $exception, $request) {
            return response()->json(
                (new ErrorResource($exception))->toArray($request),
                $exception->getHttpStatus(),
            );
        });

        // 2. Validation — 422
        $exceptions->render(function (ValidationException $exception, $request) {
            $appException = new SharedValidationException(
                details: collect($exception->errors())->map(fn ($messages, $field) => [
                    'field' => $field,
                    'message' => implode('; ', $messages),
                ])->values()->toArray(),
            );

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                422,
            );
        });

        // 3. Unauthenticated — 401
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return;
            }

            $appException = new class('UNAUTHENTICATED', 'Bạn cần đăng nhập để tiếp tục') extends AppException
            {
                public function getHttpStatus(): int
                {
                    return 401;
                }
            };

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                401,
            );
        });

        // 4. Forbidden — 403
        $exceptions->render(function (AuthorizationException $exception, $request) {
            $appException = new class('FORBIDDEN', 'Bạn không có quyền thực hiện hành động này') extends AppException
            {
                public function getHttpStatus(): int
                {
                    return 403;
                }
            };

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                403,
            );
        });

        // 5. Model not found — 404
        $exceptions->render(function (ModelNotFoundException $exception, $request) {
            $modelName = class_basename($exception->getModel());
            $message = "Không tìm thấy {$modelName}";

            $appException = new class('MODEL_NOT_FOUND', $message) extends AppException
            {
                public function getHttpStatus(): int
                {
                    return 404;
                }
            };

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                404,
            );
        });

        // 6. Route not found — 404
        $exceptions->render(function (NotFoundHttpException $exception, $request) {
            $appException = new class('NOT_FOUND', 'Đường dẫn không tồn tại') extends AppException
            {
                public function getHttpStatus(): int
                {
                    return 404;
                }
            };

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                404,
            );
        });

        // 7. Rate limit — 429
        $exceptions->render(function (TooManyRequestsHttpException $exception, $request) {
            $appException = new class('TOO_MANY_REQUESTS', 'Bạn đã gửi quá nhiều yêu cầu, vui lòng thử lại sau') extends AppException
            {
                public function getHttpStatus(): int
                {
                    return 429;
                }
            };

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                429,
            );
        });

        // 8. Database query error — 500
        $exceptions->render(function (QueryException $exception, $request) {
            logger()->error($exception->getMessage(), ['exception' => $exception]);

            $appException = new class('DATABASE_ERROR', 'Có lỗi hệ thống xảy ra, vui lòng thử lại sau') extends AppException
            {
                public function getHttpStatus(): int
                {
                    return 500;
                }
            };

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                500,
            );
        });

        // 9. Fallback — 500
        $exceptions->render(function (Throwable $exception, $request) {
            logger()->error($exception->getMessage(), ['exception' => $exception]);

            $appException = new class('INTERNAL_ERROR', 'Có lỗi hệ thống xảy ra') extends AppException
            {
                public function getHttpStatus(): int
                {
                    return 500;
                }
            };

            return response()->json(
                (new ErrorResource($appException))->toArray($request),
                500,
            );
        });
    


RateLimiter::for("api", fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
RateLimiter::for("auth", fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
RateLimiter::for("strict", fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));

})->create();
