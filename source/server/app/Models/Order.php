<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // BẮT BUỘC SỬ DỤNG DÒNG NÀY

class Order extends Model
{
    protected $collection = 'orders';

    protected $fillable = [
        'user_id',       // ID của học viên
        'course_id',     // Chính là courseGroupId của khóa học
        'price_paid',    // Số tiền đã thanh toán (0 nếu là khóa miễn phí)
        'payment_method',// Phương thức thanh toán (VNPay, Momo, Free...)
        'status',        // SUCCESS, PENDING, CANCELED
        'progress',      // Tiến độ học tập (0 - 100%)
    ];

    protected $casts = [
        'price_paid' => 'integer',
        'progress' => 'integer',
    ];

    // Mối quan hệ: Một đơn hàng thuộc về một User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Mối quan hệ: Một đơn hàng liên kết với một Khóa học
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'courseGroupId');
    }
}