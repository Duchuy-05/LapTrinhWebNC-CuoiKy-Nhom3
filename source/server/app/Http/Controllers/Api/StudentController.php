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

}