<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function Home(Request $request)
    {
        // Dùng guard 'sanctum' tường minh — tránh phụ thuộc vào AUTH_GUARD mặc định
        // Trả về null nếu chưa đăng nhập → trang chủ vẫn hiển thị, chỉ thiếu phần gợi ý
        $userId    = auth('sanctum')->id();
        $baseQuery = Course::where('status', 'PUBLISHED');

        // ── Tham số phân trang ────────────────────────────────────────────────
        $perPage         = 4; // số khóa học mỗi trang
        $trendingPage    = max(1, (int) $request->input('trending_page', 1));
        $mostLovedPage   = max(1, (int) $request->input('most_loved_page', 1));

        // ── Trending (mới nhất) ───────────────────────────────────────────────
        $trendingTotal   = (clone $baseQuery)->count();
        $trendingCourses = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->skip(($trendingPage - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // ── Best sellers (bán chạy) — không phân trang, chỉ lấy top 8 ────────
        $bestSellers = (clone $baseQuery)->orderBy('student_count', 'desc')->limit(8)->get();

        // ── Most Loved (Bayesian rating) — phân trang in-memory ──────────────
        $C = (clone $baseQuery)->avg('rating_score') ?? 0;
        $m = (clone $baseQuery)->avg('rating_count') ?? 0;
        $m = $m == 0 ? 1 : $m;

        $allMostLoved = (clone $baseQuery)->get()->map(function ($course) use ($C, $m) {
            $v = $course->rating_count ?? 0;
            $R = $course->rating_score ?? 0;
            $course->bayesian_score = (($v * $R) + ($m * $C)) / ($v + $m);
            return $course;
        })->sortByDesc('bayesian_score')->values();

        $mostLovedTotal  = $allMostLoved->count();
        $mostLoved       = $allMostLoved->slice(($mostLovedPage - 1) * $perPage, $perPage)->values();

        // ── Gợi ý cá nhân hóa — chỉ tính khi đã đăng nhập ───────────────────
        $recommendedCourses = collect();

        if ($userId) {
            try {
                $purchasedCourseIds = Order::where('user_id', $userId)
                                          ->where('status', 'SUCCESS')
                                          ->pluck('course_id')
                                          ->toArray();

                if (!empty($purchasedCourseIds)) {
                    $purchasedCourses = Course::whereIn('courseGroupId', $purchasedCourseIds)->get(['tags']);

                    $favoriteTags = $purchasedCourses->pluck('tags')
                        ->filter()
                        ->flatMap(fn($t) => array_map('trim', explode(',', $t)))
                        ->unique()->filter()->toArray();

                    if (!empty($favoriteTags)) {
                        $recommendedCourses = (clone $baseQuery)
                            ->whereNotIn('courseGroupId', $purchasedCourseIds)
                            ->where(function ($q) use ($favoriteTags) {
                                foreach ($favoriteTags as $tag) {
                                    $q->orWhere('tags', 'like', '%' . $tag . '%');
                                }
                            })
                            ->limit(8)->get();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Lỗi thuật toán đề xuất: ' . $e->getMessage());
            }
        }

        return response()->json([
            'trending' => [
                'data'         => $this->attachAuthorNames($trendingCourses),
                'current_page' => $trendingPage,
                'per_page'     => $perPage,
                'total'        => $trendingTotal,
                'last_page'    => (int) ceil($trendingTotal / $perPage),
            ],
            'mostLoved' => [
                'data'         => $this->attachAuthorNames($mostLoved),
                'current_page' => $mostLovedPage,
                'per_page'     => $perPage,
                'total'        => $mostLovedTotal,
                'last_page'    => (int) ceil($mostLovedTotal / $perPage),
            ],
            'bestSellers' => $this->attachAuthorNames($bestSellers),
            'recommended' => $this->attachAuthorNames($recommendedCourses),
        ]);
    }

    public function search(Request $request)
    {
        $keyword  = $request->input('keyword');
        $minPrice = $request->input('minPrice');
        $maxPrice = $request->input('maxPrice');
        $sortBy   = $request->input('sortBy', 'newest');

        $query = Course::where('status', 'PUBLISHED');

        // 1. Lọc theo từ khóa
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'regexp', '/' . $keyword . '/i')
                  ->orWhere('tags', 'regexp', '/' . $keyword . '/i');
            });
        }

        // 2. Lọc theo khoảng giá (dựa trên giá hiệu lực: discountPrice nếu có, không thì price)
        //    Vì MongoDB không hỗ trợ computed field trong where, ta lấy về rồi lọc in-memory.
        $courses = $query->get();

        if ($minPrice !== null && $minPrice !== '') {
            $courses = $courses->filter(fn($c) => $this->effectivePrice($c) >= (int) $minPrice);
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $courses = $courses->filter(fn($c) => $this->effectivePrice($c) <= (int) $maxPrice);
        }

        // 3. Sắp xếp
        $courses = match ($sortBy) {
            'highest_rated' => $courses->sortByDesc('rating_score'),
            'popular'       => $courses->sortByDesc('student_count'),
            'price_asc'     => $courses->sortBy(fn($c) => $this->effectivePrice($c)),
            'price_desc'    => $courses->sortByDesc(fn($c) => $this->effectivePrice($c)),
            default         => $courses->sortByDesc('created_at'),
        };

        return response()->json($this->attachAuthorNames($courses->values()));
    }

    /**
     * Gắn thêm trường author_name vào mỗi khóa học.
     * Dùng 1 query duy nhất (whereIn) để tránh N+1.
     */
    private function attachAuthorNames($courses)
    {
        $authorIds = $courses->pluck('authorId')->filter()->unique()->values()->toArray();

        if (empty($authorIds)) return $courses;

        // Lấy map: authorId → name (chỉ 1 query)
        $authorMap = User::whereIn('_id', $authorIds)
                         ->get(['_id', 'name'])
                         ->keyBy('_id')
                         ->map(fn($u) => $u->name);

        return $courses->map(function ($course) use ($authorMap) {
            $data = is_array($course) ? $course : $course->toArray();
            $data['author_name'] = $authorMap[$data['authorId'] ?? ''] ?? 'Giảng viên';
            return $data;
        });
    }

    /**
     * Tính giá hiệu lực của khóa học:
     * - Nếu đang khuyến mãi (discountPrice !== null) → trả về discountPrice
     * - Nếu không → trả về price gốc
     */
    private function effectivePrice($course): int
    {
        return $course->discountPrice !== null ? (int) $course->discountPrice : (int) $course->price;
    }
}