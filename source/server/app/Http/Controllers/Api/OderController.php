<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Course;

class OderController extends Controller
{
    public function myCourses(Request $request)
    {
        $userId = auth()->id();

        $orders = Order::where('user_id', $userId)->get();

        if ($orders->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $enrolledCourseIds = $orders->pluck('course_id')->toArray();

        $courses = Course::whereIn('courseGroupId', $enrolledCourseIds)
                         ->where('status', 'PUBLISHED')
                         ->get();

        $coursesWithProgress = $courses->map(function ($course) use ($orders) {
            $order = $orders->firstWhere('course_id', $course->courseGroupId);

            $courseData = $course->toArray();
            $courseData['progress'] = $order ? ($order->progress ?? 0) : 0;

            return $courseData;
        });

        return response()->json(['data' => $coursesWithProgress]);
    }

    public function enrollCourse(Request $request, $courseGroupId)
    {
        $userId = auth()->id();

        $isEnrolled = Order::where('user_id', $userId)
                        ->where('course_id', $courseGroupId)
                        ->where('status', 'SUCCESS')
                        ->exists();

        if ($isEnrolled) {
            return response()->json(['message' => 'Bạn đã sở hữu khóa học này rồi!'], 400);
        }

        $course = Course::where('courseGroupId', $courseGroupId)->where('status', 'PUBLISHED')->first();
        if (!$course) {
            return response()->json(['message' => 'Khóa học không tồn tại hoặc chưa xuất bản'], 404);
        }

        // Tính giá hiệu lực: nếu có khuyến mãi thì dùng discountPrice, không thì dùng price gốc
        $effectivePrice = $course->discountPrice !== null ? (int) $course->discountPrice : (int) ($course->price ?? 0);

        // Chỉ cho phép enroll miễn phí qua API này khi giá hiệu lực = 0
        if ($effectivePrice > 0) {
            return response()->json(['message' => 'Khóa học này có phí, vui lòng thanh toán qua trang checkout.'], 400);
        }

        $order = Order::create([
            'user_id'        => $userId,
            'course_id'      => $courseGroupId,
            'price_paid'     => 0,
            'payment_method' => 'FREE',
            'status'         => 'SUCCESS',
            'progress'       => 0,
        ]);

        $course->increment('student_count');

        return response()->json([
            'message' => 'Đăng ký khóa học thành công!',
            'data'    => $order,
        ], 201);
    }

    public function processCheckout(Request $request, $courseGroupId, \App\Services\PayOSService $payOsService)
    {
        $userId        = auth()->id();
        $paymentMethod = $request->input('paymentMethod');

        $course = Course::where('courseGroupId', $courseGroupId)->where('status', 'PUBLISHED')->first();
        if (!$course) {
            return response()->json(['message' => 'Khóa học không tồn tại'], 404);
        }

        $currentPrice      = $course->price ?? 0;
        $isDiscountActive  = $course->discountPrice !== null;
        $finalPrice        = $isDiscountActive ? $course->discountPrice : $currentPrice;

        if ($finalPrice <= 0) {
            return response()->json(['message' => 'Khóa học miễn phí, vui lòng dùng API enroll'], 400);
        }

        // Tạo đơn hàng tạm thời (PENDING)
        $order                  = new Order();
        $order->user_id         = $userId;
        $order->course_id       = $courseGroupId;
        $order->price_paid      = $finalPrice;
        $order->payment_method  = $paymentMethod;
        $order->status          = 'PENDING';
        $order->save();

        if ($paymentMethod === 'payos') {
            try {
                $payUrl = $payOsService->createPaymentLink($order);
                return response()->json(['payUrl' => $payUrl]);
            } catch (\Exception $e) {
                // Rollback đơn hàng PENDING nếu tạo link thất bại
                $order->delete();
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Phương thức thanh toán không hợp lệ'], 400);
    }

    public function handlePayOSWebhook(Request $request, \App\Services\PayOSService $payOsService)
    {
        try {
            $body = $request->all();

            // Bước 1: Xác thực chữ ký – SDK v2 trả về object WebhookData
            $webhookData = $payOsService->verifyWebhookData($body);

            // Bước 2: SDK v2 trả về object → dùng -> thay vì ['key']
            if (isset($webhookData->code) && $webhookData->code === '00') {
                $orderCode  = $webhookData->orderCode;
                $amountPaid = $webhookData->amount;

                // Bước 3: Tìm đơn hàng PENDING khớp với orderCode
                $order = Order::where('transaction_id', $orderCode)
                               ->where('status', 'PENDING')
                               ->first();

                if ($order) {
                    // Bước 4: Kiểm tra số tiền khớp
                    if ((int) $order->price_paid === (int) $amountPaid) {
                        $order->status = 'SUCCESS';
                        $order->save();

                        $course = Course::where('courseGroupId', $order->course_id)->first();
                        if ($course) {
                            $course->increment('student_count');
                        }

                        return response()->json([
                            "error"   => 0,
                            "message" => "Xác nhận thanh toán và mở khóa học thành công",
                            "data"    => null,
                        ]);
                    } else {
                        \Log::warning("PayOS: Đơn hàng {$order->id} sai số tiền. Yêu cầu: {$order->price_paid}, Thực nhận: {$amountPaid}");

                        return response()->json([
                            "error"   => 0,
                            "message" => "Ghi nhận giao dịch, nhưng số tiền không khớp đơn hàng",
                        ]);
                    }
                }
            }

            return response()->json(["error" => 0, "message" => "Đơn hàng đã xử lý hoặc không tồn tại"]);

        } catch (\Exception $e) {
            \Log::error("PayOS Webhook Error: " . $e->getMessage());

            return response()->json([
                "error"   => -1,
                "message" => "Lỗi xác thực Webhook: " . $e->getMessage(),
            ]);
        }
    }
}