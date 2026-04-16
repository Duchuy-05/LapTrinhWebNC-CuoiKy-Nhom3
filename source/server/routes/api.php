<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontendApiController;
use App\Http\Controllers\Api\CourseController; 
use App\Http\Controllers\Api\VideoUploadController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\OderController;
use App\Http\Controllers\Api\LearnController;
// ==========================================
// VÙNG API CÔNG CỘNG (Không cần đăng nhập)
// ==========================================
Route::get('/courses', function () {
    $courses = \App\Models\Course::where('status', 'published')->get();
    return response()->json($courses);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [FrontendApiController::class, 'register']);

// ==========================================
// VÙNG API ĐƯỢC BẢO VỆ (Bắt buộc phải có Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
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
        Route::post('/upload-video', VideoUploadController::class); // Upload video bài giảng
        Route::post('/upload-image', ImageUploadController::class); // Upload hình ảnh minh họa
        Route::put('/lecturer/courses/{courseGroupId}/price', [\App\Http\Controllers\Api\CourseController::class, 'updatePrice']); // Cập nhật giá khóa học
    });

    Route::prefix('student')->group(function () {
        Route::get('/home', [StudentController::class, 'Home']);
        Route::get('/my-courses', [OderController::class, 'myCourses']);
        Route::get('/student/courses/{courseGroupId}/learn', [LearnController::class, 'showCourseContent']);
    });
});