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
        $keyword = $request->input('keyword');
        $minPrice = $request->input('minPrice');
        $maxPrice = $request->input('maxPrice');
        $sortBy = $request->input('sortBy', 'newest'); // Mặc định là mới nhất

        // Bắt đầu query cơ bản
        $query = Course::where('status', 'PUBLISHED');

        // 1. Lọc theo từ khóa (nếu có)
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'regexp', '/' . $keyword . '/i')
                  ->orWhere('tags', 'regexp', '/' . $keyword . '/i');
            });
        }

        // 2. Lọc theo khoảng giá
        // Lưu ý: Ép kiểu về số nguyên (int) để so sánh chính xác trong MongoDB
        if ($minPrice !== null && $minPrice !== '') {
            $query->where('price', '>=', (int) $minPrice);
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('price', '<=', (int) $maxPrice);
        }

        // 3. Sắp xếp
        switch ($sortBy) {
            case 'highest_rated':
                $query->orderBy('rating_score', 'desc');
                break;
            case 'popular':
                $query->orderBy('student_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $courses = $query->get();

        return response()->json($courses);
    }
}