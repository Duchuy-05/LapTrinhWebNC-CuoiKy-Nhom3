<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontendApiController;

// ==========================================
// VÙNG API CÔNG CỘNG CHO REACT
// ==========================================
Route::get('/courses', function () {
    $courses = \App\Models\Course::where('status', 'published')->get();
    return response()->json($courses);
});

Route::post('/login', [FrontendApiController::class, 'login']);
Route::post('/register', [FrontendApiController::class, 'register']);
Route::post('/create-order', [FrontendApiController::class, 'createOrder']);
Route::post('/webhook', [FrontendApiController::class, 'bankingWebhook']);
Route::get('/check-order/{id}', [FrontendApiController::class, 'checkOrder']);
Route::post('/my-courses', [FrontendApiController::class, 'myCourses']);

// Đường dẫn yêu cầu phải có Token mới vào được (Ví dụ: Lấy thông tin user hiện tại)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});