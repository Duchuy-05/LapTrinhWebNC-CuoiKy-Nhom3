<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // Tắt kiểm tra CSRF cho tất cả các API để React có thể gửi dữ liệu vào
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Đăng ký alias 'role' để dùng trong route file:
        //   ->middleware('role:lecturer')
        //   ->middleware('role:lecturer,admin')
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();