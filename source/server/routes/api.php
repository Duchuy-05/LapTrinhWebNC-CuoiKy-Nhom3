<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontendApiController;
use App\Http\Controllers\Api\CourseController; // Đừng quên import CourseController

// ==========================================
// VÙNG API CÔNG CỘNG (Không cần đăng nhập)
// ==========================================
Route::get('/courses', function () {
    $courses = \App\Models\Course::where('status', 'published')->get();
    return response()->json($courses);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [FrontendApiController::class, 'register']);
Route::post('/create-order', [FrontendApiController::class, 'createOrder']);
Route::post('/webhook', [FrontendApiController::class, 'bankingWebhook']);
Route::get('/check-order/{id}', [FrontendApiController::class, 'checkOrder']);
Route::post('/my-courses', [FrontendApiController::class, 'myCourses']);

// ==========================================
// VÙNG API ĐƯỢC BẢO VỆ (Bắt buộc phải có Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // API lấy thông tin user hiện tại
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Group API dành cho Giảng viên (Lecturer)
    Route::prefix('lecturer/courses')->group(function () {
        Route::get('/', [CourseController::class, 'index']); // Lấy danh sách khóa học
        Route::post('/', [CourseController::class, 'store']); // Khởi tạo bản nháp
        Route::get('/{courseGroupId}/draft', [CourseController::class, 'showDraft']); // Xem bản nháp
        Route::put('/{courseGroupId}/draft', [CourseController::class, 'updateDraft']); // Sửa bản nháp
        Route::post('/{courseGroupId}/publish', [CourseController::class, 'publish']); // Xuất bản
        Route::post('/{courseGroupId}/unpublish', [CourseController::class, 'unpublish']); // Ngừng xuất bản
    });

});