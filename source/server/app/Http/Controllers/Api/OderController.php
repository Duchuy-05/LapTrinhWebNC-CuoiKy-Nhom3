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

        // Chỉ lấy đơn SUCCESS — loại bỏ PENDING (chưa thanh toán) và CANCELED
        $orders = Order::where('user_id', $userId)
                       ->where('status', 'SUCCESS')
                       ->get();

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
            'paid_at'        => now(), // BỔ SUNG: Lưu thời gian lúc nhận miễn phí
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

        // Chặn người đã mua thành công khỏi thanh toán lại
        $alreadyOwned = Order::where('user_id', $userId)
                             ->where('course_id', $courseGroupId)
                             ->where('status', 'SUCCESS')
                             ->exists();
        if ($alreadyOwned) {
            return response()->json(['message' => 'Bạn đã sở hữu khóa học này rồi!'], 400);
        }

        $currentPrice     = $course->price ?? 0;
        $isDiscountActive = $course->discountPrice !== null;
        $finalPrice       = $isDiscountActive ? $course->discountPrice : $currentPrice;

        if ($finalPrice <= 0) {
            return response()->json(['message' => 'Khóa học miễn phí, vui lòng dùng API enroll'], 400);
        }

        // Tái sử dụng đơn PENDING còn tồn tại (tránh tạo nhiều đơn trùng khi bấm lại)
        $existingPending = Order::where('user_id', $userId)
                                ->where('course_id', $courseGroupId)
                                ->where('status', 'PENDING')
                                ->latest()
                                ->first();

        if ($existingPending && $existingPending->transaction_id) {
            // Đã có đơn PENDING với transaction_id → thử tạo lại link PayOS từ đơn cũ
            try {
                $payUrl = $payOsService->createPaymentLinkFromExisting($existingPending);
                return response()->json(['payUrl' => $payUrl]);
            } catch (\Exception $e) {
                // Nếu link cũ hết hạn hoặc lỗi → hủy đơn cũ, tạo đơn mới bên dưới
                $existingPending->update(['status' => 'CANCELED']);
            }
        }

        // Tạo đơn hàng PENDING mới
        $order                 = new Order();
        $order->user_id        = $userId;
        $order->course_id      = $courseGroupId;
        $order->price_paid     = $finalPrice; // Đã chuẩn tên biến
        $order->payment_method = $paymentMethod;
        $order->status         = 'PENDING';
        $order->save();

        if ($paymentMethod === 'payos') {
            try {
                $payUrl = $payOsService->createPaymentLink($order);
                return response()->json(['payUrl' => $payUrl]);
            } catch (\Exception $e) {
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

            // Bước 1: Xác thực chữ ký
            $webhookData = $payOsService->verifyWebhookData($body);

            if (isset($webhookData->code) && $webhookData->code === '00') {
                $orderCode  = $webhookData->orderCode;
                $amountPaid = $webhookData->amount;

                // Bước 2: Tìm đơn PENDING khớp orderCode
                $order = Order::where('transaction_id', $orderCode)
                               ->where('status', 'PENDING')
                               ->first();

                if (!$order) {
                    // Đơn đã xử lý trước đó (retry webhook) → idempotent, trả OK
                    return response()->json(["error" => 0, "message" => "Đơn hàng đã xử lý hoặc không tồn tại"]);
                }

                // Bước 3: Kiểm tra số tiền
                if ((int) $order->price_paid !== (int) $amountPaid) {
                    \Log::warning("PayOS: Đơn {$order->id} sai số tiền. Yêu cầu: {$order->price_paid}, Thực nhận: {$amountPaid}");
                    return response()->json(["error" => 0, "message" => "Ghi nhận giao dịch, nhưng số tiền không khớp"]);
                }

                // Bước 4: Cập nhật trạng thái (chỉ khi vẫn là PENDING — chống race condition)
                $updated = Order::where('_id', $order->id)
                                 ->where('status', 'PENDING')
                                 ->update([
                                     'status'  => 'SUCCESS',
                                     'paid_at' => now() // BỔ SUNG: Ghi nhận giờ phút thanh toán từ PayOS
                                 ]);

                if ($updated > 0) {
                    // Chỉ tăng student_count khi chính xác 1 lần
                    $course = Course::where('courseGroupId', $order->course_id)->first();
                    if ($course) {
                        $course->increment('student_count');
                    }

                    \Log::info("PayOS: Mở khóa thành công đơn {$order->id}, khóa học {$order->course_id}");
                }

                return response()->json(["error" => 0, "message" => "Xác nhận thanh toán thành công", "data" => null]);
            }

            return response()->json(["error" => 0, "message" => "Không phải giao dịch thành công"]);

        } catch (\Exception $e) {
            \Log::error("PayOS Webhook Error: " . $e->getMessage());
            return response()->json(["error" => -1, "message" => "Lỗi xác thực Webhook: " . $e->getMessage()]);
        }
    }

    public function getOrderStatus(Request $request, $courseGroupId)
    {
        $userId = auth()->id();

        $order = Order::where('user_id', $userId)
                      ->where('course_id', $courseGroupId)
                      ->orderBy('created_at', 'desc')
                      ->first();

        if (!$order) {
            return response()->json(['status' => 'NOT_FOUND']);
        }

        return response()->json([
            'status'    => $order->status,   
            'pricePaid' => $order->price_paid,
        ]);
    }
}