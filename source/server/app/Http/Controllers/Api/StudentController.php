<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function Home(Request $request)
    {
        $userId = auth()->id();
        $baseQuery = Course::where('status', 'PUBLISHED');

        $trendingCourses = (clone $baseQuery)->orderBy('created_at', 'desc')->limit(4)->get();
        $bestSellers     = (clone $baseQuery)->orderBy('student_count', 'desc')->limit(4)->get();


        $C = (clone $baseQuery)->avg('rating_score') ?? 0;
        $m = (clone $baseQuery)->avg('rating_count') ?? 0;
        $m = $m == 0 ? 1 : $m;

        $mostLoved = (clone $baseQuery)->get()->map(function ($course) use ($C, $m) {
            $v = $course->rating_count ?? 0;
            $R = $course->rating_score ?? 0;

            $course->bayesian_score = (($v * $R) + ($m * $C)) / ($v + $m);
            return $course;
        })
        ->sortByDesc('bayesian_score')
        ->take(4)
        ->values();

        $recommendedCourses = []; 

        try {
            $purchasedCourseIds = Order::where('user_id', $userId)->pluck('course_id')->toArray();

            if (!empty($purchasedCourseIds)) {
                $purchasedCourses = Course::whereIn('courseGroupId', $purchasedCourseIds)->get(['tags']);
                
                $favoriteTags = $purchasedCourses->pluck('tags')
                    ->filter() 
                    ->flatMap(function ($tagsString) {
                        return array_map('trim', explode(',', $tagsString));
                    })
                    ->unique()
                    ->filter()
                    ->toArray();

                if (!empty($favoriteTags)) {
                    $recommendedCourses = (clone $baseQuery)
                        ->whereNotIn('courseGroupId', $purchasedCourseIds)
                        ->where(function($query) use ($favoriteTags) {
                            foreach ($favoriteTags as $tag) {
                                $query->orWhere('tags', 'like', '%' . $tag . '%');
                            }
                        })
                        ->limit(4)
                        ->get();
                }
            }
        } catch (\Exception $e) {
            Log::error("Lỗi thuật toán đề xuất: " . $e->getMessage());
        }

        return response()->json([
            'trending'    => $trendingCourses,
            'bestSellers' => $bestSellers,
            'mostLoved'   => $mostLoved,
            'recommended' => $recommendedCourses
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

        return response()->json($courses->values());
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