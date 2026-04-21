<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PayoutRequest extends Model
{
    // Chỉ định dùng kết nối MongoDB
    protected $connection = 'mongodb';
    
    // Tên collection (bảng) trong database
    protected $collection = 'payout_requests';

    // Các trường dữ liệu được phép thêm/sửa
    protected $fillable = [
        'user_id', 
        'amount', 
        'bank_name', 
        'account_name', 
        'account_number', 
        'status', 
        'receipt_image', 
        'admin_note'
    ];

    // Mối quan hệ: Một yêu cầu rút tiền thuộc về một Giảng viên (User)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}