<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseAccessService; 
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    protected $accessService;

    // Inject Service vào Controller
    public function __construct(CourseAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * Lấy danh sách khóa học CỦA GIẢNG VIÊN ĐANG ĐĂNG NHẬP
     */
    public function index()
    {
        $courses = Course::where('authorId', auth()->id())
                         ->orderBy('created_at', 'desc')
                         ->get();
        
        $courses->transform(function ($course) {
            $course->unit_count = is_array($course->courseData) ? count($course->courseData) : 0;
            unset($course->blocks); // Tối ưu payload
            return $course;
        });
                         
        return response()->json(['data' => $courses]);
    }

    /**
     * 1. Khởi tạo một khóa học mới (DRAFT)
     */
    public function store(Request $request)
    {
        $courseGroupId = (string) Str::uuid(); 

        $course = Course::create([
            'courseGroupId' => $courseGroupId,
            'status' => 'DRAFT',
            'version' => 1,
            'title' => $request->input('title', 'Khóa học mới'),
            'courseData' => [],
            'blocks' => [],
            'authorId' => auth()->id(), // Bắt buộc lưu ID người tạo
            'price' => 0,
            'discountPrice' => null
        ]);

        if (!$course) {
            return response()->json(['message' => 'Tạo bản nháp thất bại'], 500);
        }

        return response()->json([
            'message' => 'Tạo bản nháp thành công', 
            'courseGroupId' => $courseGroupId,
            'data' => $course
        ], 201);
    }

    /**
     * 2. Lấy thông tin bản nháp (CHỈ GIẢNG VIÊN ĐÓ MỚI ĐƯỢC LẤY)
     */
    public function showDraft($courseGroupId)
    {
        $draft = Course::where('courseGroupId', $courseGroupId)
                       ->where('status', 'DRAFT')
                       ->where('authorId', auth()->id()) // Chỉ chủ khóa học mới được xem
                       ->first();

        if (!$draft) {
            return response()->json(['message' => 'Không tìm thấy bản nháp hoặc bạn không có quyền'], 404);
        }

        return response()->json(['data' => $draft]);
    }

    /**
     * 3. Lưu bản nháp (CHỈ GIẢNG VIÊN ĐÓ MỚI ĐƯỢC LƯU)
     */
    public function updateDraft(Request $request, $courseGroupId)
    {
        $draft = Course::where('courseGroupId', $courseGroupId)
                       ->where('status', 'DRAFT')
                       ->where('authorId', auth()->id()) // BẢO MẬT
                       ->first();

        if (!$draft) {
            return response()->json(['message' => 'Không tìm thấy bản nháp hoặc bạn không có quyền'], 404);
        }

        $draft->update($request->only([
            'title', 'description', 'thumbnail', 'tags', 'courseData', 'blocks', 'price', 'discountPrice'
        ]));

        return response()->json(['message' => 'Đã lưu bản nháp', 'data' => $draft]);
    }

    /**
     * 4. XUẤT BẢN KHÓA HỌC
     */
    public function publish($courseGroupId)
    {
        $draft = Course::where('courseGroupId', $courseGroupId)
                       ->where('status', 'DRAFT')
                       ->where('authorId', auth()->id()) // BẢO MẬT
                       ->first();

        if (!$draft) {
            return response()->json(['message' => 'Không tìm thấy bản nháp để xuất bản'], 404);
        }

        // Đưa bản PUBLISHED cũ về ARCHIVED
        Course::where('courseGroupId', $courseGroupId)
              ->where('status', 'PUBLISHED')
              ->update(['status' => 'ARCHIVED']);

        // Clone bản nháp thành bản chính thức
        $published = $draft->replicate(); 
        $published->status = 'PUBLISHED';
        $published->save();

        $draft->increment('version');

        return response()->json([
            'message' => 'Xuất bản thành công!', 
            'published_version' => $published->version
        ]);
    }

    /**
     * 5. Ngừng xuất bản
     */
    public function unpublish($courseGroupId)
    {
        $updated = Course::where('courseGroupId', $courseGroupId)
                         ->where('status', 'PUBLISHED')
                         ->where('authorId', auth()->id()) // BẢO MẬT
                         ->update(['status' => 'UNPUBLISHED']);

        if (!$updated) {
            return response()->json(['message' => 'Thao tác thất bại'], 400);
        }

        return response()->json(['message' => 'Đã ngừng xuất bản khóa học']);
    }

    /**
     * 6. Cập nhật giá
     */
    public function updatePrice(Request $request, $courseGroupId)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'discountPrice' => 'required|numeric|min:0',
        ]);

        // Sử dụng authorId để tránh người khác dùng postman đổi giá khóa học (ác)
        $courses = Course::where('courseGroupId', $courseGroupId)
                         ->whereIn('status', ['PUBLISHED', 'DRAFT'])
                         ->where('authorId', auth()->id())
                         ->get();

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'Không có quyền cập nhật'], 403);
        }

        foreach ($courses as $course) {
            $course->update([
                'price' => $request->price,
                'discountPrice' => $request->discountPrice
            ]);
        }

        return response()->json(['message' => 'Cập nhật giá thành công!']);
    }

    /**
     * =========================================================================
     * CÁC API DÀNH CHO VIỆC HIỂN THỊ KHÓA HỌC (ĐÃ TÁCH BIỆT LOGIC BẢO MẬT)
     * =========================================================================
     */

    /**
     * 7A. Xem khóa học DÀNH CHO GIẢNG VIÊN (Lấy full toàn bộ data)
     */
    public function showPublishedForLecturer($courseGroupId)
    {
        $published = Course::where('courseGroupId', $courseGroupId)
                           ->where('status', 'PUBLISHED')
                           ->where('authorId', auth()->id()) // Giảng viên chỉ được xem bản full của chính họ
                           ->first();

        if (!$published) {
            return response()->json(['message' => 'Không tìm thấy khóa học đã xuất bản'], 404);
        }

        return response()->json(['data' => $published]);
    }

    /**
     * 7B. Xem khóa học DÀNH CHO HỌC VIÊN (Đi qua lớp bảo vệ CourseAccessService)
     * API này nên được bọc bởi middleware auth:sanctum (nếu user đã đăng nhập) 
     * hoặc public (nếu cho khách xem giới thiệu)
     */
    public function showPublishedForStudent($courseGroupId, Request $request)
    {
        // Học viên thì không cần check authorId
        $published = Course::where('courseGroupId', $courseGroupId)
                           ->where('status', 'PUBLISHED')
                           ->first();

        if (!$published) {
            return response()->json(['message' => 'Khóa học không tồn tại hoặc chưa xuất bản'], 404);
        }

        $user = $request->user('sanctum'); // Lấy user hiện tại 

        // ĐƯA DATA QUA "MÁY QUÉT" ĐỂ CẮT XÉN PHẦN BỊ KHÓA
        $safeData = $this->accessService->getSanitizedCourseData($published, $user);

        return response()->json([
            'data' => [
                'id' => $published->id,
                'courseGroupId' => $published->courseGroupId,
                'title' => $published->title,
                'price' => $published->price,
                'discountPrice' => $published->discountPrice,
                'rating_score' => $published->rating_score ?? 0,
                'student_count' => $published->student_count ?? 0,
                // Trả về courseData đã được lọc sạch sẽ
                'courseData' => $safeData 
            ]
        ]);
    }

    public function getPublicDetail(Request $request, $courseGroupId)
    {
        // Lấy thông tin khóa học
        $course = Course::where('courseGroupId', $courseGroupId)->where('status', 'PUBLISHED')->first();
        if (!$course) return response()->json(['message' => 'Không tìm thấy khóa học'], 404);

        // Kiểm tra xem User có đang gửi Token không (để biết đã mua chưa)
        $userId = auth('sanctum')->id();
        $isEnrolled = false;
        
        if ($userId) {
            $order = Order::where('user_id', $userId)
                        ->where('course_id', $courseGroupId)
                        ->where('status', 'SUCCESS')
                        ->first();
            $isEnrolled = !!$order;
        }

        $isFree = ($course->price == 0 || $course->discountPrice == 0);

        return response()->json([
            'data' => $course,
            'isEnrolled' => $isEnrolled,
            'isFree' => $isFree
        ]);
    }
}