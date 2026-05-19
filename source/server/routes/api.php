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
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\StatisticsController;

// ==========================================
// VÙNG API CÔNG CỘNG (Không cần đăng nhập)
// ==========================================

Route::get('/courses', function () {
    $courses = \App\Models\Course::where('status', 'published')->get();
    return response()->json($courses);
});

// THÊM MỚI: API xem chi tiết khóa học (Dành cho cả khách vãng lai và học viên)
// Laravel Sanctum sẽ tự động nhận diện Token (nếu có) để mở khóa video ở Controller

Route::post('/login', [AuthController::class, 'login']);
//Route::post('/register', [FrontendApiController::class, 'register']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/register/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login/google', [AuthController::class, 'googleLogin']);

// Trang chủ & tìm kiếm: công khai, nhưng dùng 'sanctum' guard để nhận token nếu có
// Laravel sẽ tự populate auth()->user() nếu Bearer token hợp lệ, không bắt lỗi nếu thiếu
Route::get('/student/home', [StudentController::class, 'Home']);
Route::get('/courses/search', [StudentController::class, 'search']);
Route::get('/student/courses/{courseGroupId}/learn', [LearnController::class, 'showCourseContent']);
Route::get('/courses/{courseGroupId}', [CourseController::class, 'showPublishedForStudent']);
Route::get('/courses/{courseGroupId}/detail', [CourseController::class, 'getPublicDetail']);
Route::post('/webhook/payos', [OderController::class, 'handlePayOSWebhook']);
Route::post('/webhook', [\App\Http\Controllers\Admin\OrderController::class, 'payosWebhook']);
// ==========================================
// VÙNG API ĐƯỢC BẢO VỆ (Bắt buộc phải có Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ==========================================
    // VÙNG API GIẢNG VIÊN (Bắt buộc role: lecturer hoặc admin)
    // ==========================================
    Route::middleware('role:lecturer,admin')->group(function () {

        Route::get('/lecturer/statistics', [StatisticsController::class, 'getStatistics']);

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
            Route::post('/lecturer/upload-image', [CourseController::class, 'uploadImage']);
            Route::get('/{courseGroupId}/published', [CourseController::class, 'showPublishedForLecturer']); 
        });

    }); // end role:lecturer,admin

    // ==========================================
    // VÙNG API HỌC VIÊN (Bắt buộc đăng nhập, mọi role đều dùng được)
    // ==========================================
    Route::prefix('student')->group(function () {
        Route::get('/my-courses', [OderController::class, 'myCourses']);
        Route::get('/courses/{courseGroupId}/order-status', [OderController::class, 'getOrderStatus']);
        
        Route::post('/enroll/{courseGroupId}', [OderController::class, 'enrollCourse']);
        Route::post('/courses/{courseGroupId}/progress', [LearnController::class, 'updateProgress']);
        Route::post('/courses/{courseGroupId}/comment', [CommentController::class, 'addCourseReview']);
        Route::post('/courses/{courseGroupId}/checkout', [OderController::class, 'processCheckout'])->middleware('auth:sanctum');
    });

});