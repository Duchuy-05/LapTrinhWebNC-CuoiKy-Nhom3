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

// THÊM MỚI: API xem chi tiết khóa học (Dành cho cả khách vãng lai và học viên)
// Laravel Sanctum sẽ tự động nhận diện Token (nếu có) để mở khóa video ở Controller
Route::get('/courses/{courseGroupId}', [CourseController::class, 'showPublishedForStudent']);

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
    // Mẹo: Nếu bạn đã tạo middleware CheckAdmin, hãy bọc thêm nó ở đây (VD: middleware(['auth:sanctum', 'admin']))
    Route::prefix('lecturer/courses')->group(function () {
        Route::get('/', [CourseController::class, 'index']); 
        Route::post('/', [CourseController::class, 'store']); 
        Route::get('/{courseGroupId}/draft', [CourseController::class, 'showDraft']); 
        Route::put('/{courseGroupId}/draft', [CourseController::class, 'updateDraft']); 
        Route::post('/{courseGroupId}/publish', [CourseController::class, 'publish']); 
        Route::post('/{courseGroupId}/unpublish', [CourseController::class, 'unpublish']); 
        Route::post('/upload-video', VideoUploadController::class); 
        Route::post('/upload-image', ImageUploadController::class); 
        Route::put('/{courseGroupId}/price', [CourseController::class, 'updatePrice']); 
        
        // CẬP NHẬT: Trỏ vào hàm dành riêng cho Giảng viên (Xem full data, không bị cắt xén)
        Route::get('/{courseGroupId}/published', [CourseController::class, 'showPublishedForLecturer']); 
    });

    // Group API dành cho Học viên (Student)
    Route::prefix('student')->group(function () {
        Route::get('/home', [StudentController::class, 'Home']);
        Route::get('/my-courses', [OderController::class, 'myCourses']);
        
        Route::get('/courses/{courseGroupId}/learn', [LearnController::class, 'showCourseContent']);
    });
});