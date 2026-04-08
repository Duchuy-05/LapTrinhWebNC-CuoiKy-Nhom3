<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Khai báo 2 đường dẫn mở 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Đường dẫn yêu cầu phải có Token mới vào được (Ví dụ: Lấy thông tin user hiện tại)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});