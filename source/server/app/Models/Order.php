<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // Nhớ đổi sang lõi MongoDB

class Order extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'user_id',       // ID của học viên mua khóa học
        'course_id',     // ID của khóa học được mua
        'amount',        // Số tiền giao dịch
        'payment_method',// Phương thức thanh toán (Chuyển khoản, Momo, v.v.)
        'status',        // Trạng thái: pending (Chờ duyệt), completed (Thành công), cancelled (Đã hủy)
    ];

    // Mối quan hệ: Một đơn hàng thuộc về một Người dùng (Học viên)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Mối quan hệ: Một đơn hàng thuộc về một Khóa học
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}