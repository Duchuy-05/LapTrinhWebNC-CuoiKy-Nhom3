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
            'discountPrice' => null  // null = chưa có khuyến mãi
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

        // Khi soạn thảo chỉ cho phép cập nhật giá gốc (price).
        // discountPrice chỉ được đặt sau khi khóa học đã PUBLISHED.
        $draft->update($request->only([
            'title', 'description', 'thumbnail', 'tags', 'courseData', 'blocks', 'price'
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
     * 6. Cập nhật giá khuyến mãi (CHỈ cho khóa học ĐÃ PUBLISHED)
     *    discountPrice = null  => Không khuyến mãi
     *    discountPrice = <số> => Giá sau khuyến mãi (phải < price)
     */
    public function updatePrice(Request $request, $courseGroupId)
    {
        $request->validate([
            'discountPrice' => 'nullable|numeric|min:0',
        ]);

        // Lấy bản PUBLISHED của khóa học (chỉ owner mới được cập nhật)
        $courses = Course::where('courseGroupId', $courseGroupId)
                         ->whereIn('status', ['PUBLISHED', 'DRAFT'])
                         ->where('authorId', auth()->id())
                         ->get();

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'Không có quyền cập nhật'], 403);
        }

        $discountPrice = $request->discountPrice !== null ? (int) $request->discountPrice : null;

        // Validate: giá khuyến mãi phải nhỏ hơn giá gốc
        $published = $courses->firstWhere('status', 'PUBLISHED');
        if ($discountPrice !== null && $published && $discountPrice >= $published->price) {
            return response()->json(['message' => 'Giá khuyến mãi phải nhỏ hơn giá gốc!'], 422);
        }

        foreach ($courses as $course) {
            $course->update(['discountPrice' => $discountPrice]);
        }

        return response()->json(['message' => 'Cập nhật giá khuyến mãi thành công!']);
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

        // Miễn phí nếu: giá gốc = 0, HOẶC đang khuyến mãi về 0đ
        $isFree = ($course->price == 0) || ($course->discountPrice !== null && $course->discountPrice == 0);

        return response()->json([
            'data' => $course,
            'isEnrolled' => $isEnrolled,
            'isFree' => $isFree
        ]);
    }
    /**
     * API Thống kê dành cho Giảng viên
     */
    public function getStatistics()
    {
        $authorId = auth()->id();

        // 1. ĐẾM TRẠNG THÁI KHÓA HỌC
        $draftCount = Course::where('authorId', $authorId)->where('status', 'DRAFT')->count();
        $publishedCount = Course::where('authorId', $authorId)->where('status', 'PUBLISHED')->count();
        $unpublishCount = Course::where('authorId', $authorId)->where('status', 'UNPUBLISHED')->count();

        // 2. ĐẾM KHÓA HỌC XUẤT BẢN THEO NGÀY
        $publishedCourses = Course::where('authorId', $authorId)
            ->where('status', 'PUBLISHED')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $dailyData = [];
        foreach ($publishedCourses as $course) {
            $date = \Carbon\Carbon::parse($course->created_at)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d');
            if (!isset($dailyData[$date])) $dailyData[$date] = 0;
            $dailyData[$date]++;
        }
        ksort($dailyData); 
        $dailyPublished = [];
        foreach ($dailyData as $date => $total) {
            $dailyPublished[] = ['date' => $date, 'total' => $total];
        }

        // ==========================================
        // TÍNH TOÁN DOANH THU & HỌC VIÊN
        // ==========================================
        
        // A. Lấy tất cả ID khóa học của giảng viên này
        $lecturerCourseIds = Course::where('authorId', $authorId)->pluck('courseGroupId')->toArray();
        
        // B. Tổng số học viên (Lấy từ cột student_count)
        $totalStudents = Course::where('authorId', $authorId)->sum('student_count');

        // C. Tính doanh thu từ bảng Order
        $orders = Order::whereIn('course_id', $lecturerCourseIds)
                       ->where('status', 'SUCCESS')
                       ->get();

        $totalRevenue = 0;
        $monthlyData = [];

        foreach ($orders as $order) {
            $totalRevenue += $order->price_paid; // Cộng dồn tổng doanh thu
            
            // Lấy năm-tháng để gom nhóm (VD: 2024-05)
            $sortKey = \Carbon\Carbon::parse($order->created_at)->timezone('Asia/Ho_Chi_Minh')->format('Y-m');
            if (!isset($monthlyData[$sortKey])) {
                $monthlyData[$sortKey] = 0;
            }
            $monthlyData[$sortKey] += $order->price_paid;
        }

        // Sắp xếp các tháng theo thứ tự thời gian tăng dần
        ksort($monthlyData);

        $monthlyRevenue = [];
        foreach ($monthlyData as $key => $revenue) {
            // Format lại thành MM/YYYY cho Frontend hiển thị đẹp
            $displayMonth = \Carbon\Carbon::createFromFormat('Y-m', $key)->format('m/Y');
            $monthlyRevenue[] = [
                'month' => $displayMonth,
                'revenue' => $revenue
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status_counts' => [
                    'DRAFT' => $draftCount,
                    'PUBLISHED' => $publishedCount,
                    'UNPUBLISHED' => $unpublishCount,
                ],
                'daily_published' => $dailyPublished,
                'summary' => [
                    'total_students' => $totalStudents,
                    'total_revenue' => $totalRevenue
                ],
                'monthly_revenue' => $monthlyRevenue
            ]
        ]);
    }
}