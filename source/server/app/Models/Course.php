<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Course extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'courses';

    protected $fillable = [
        'category_id', // Thuộc danh mục nào
        'teacher_id',  // Ai là người dạy (Liên kết với bảng users)
        'title',       // Tên khóa học
        'slug',        // Đường dẫn SEO
        'price',       // Giá tiền
        'description', // Mô tả chi tiết (Sau này dùng CKEditor ở đây)
        'thumbnail',   // Hình ảnh đại diện khóa học
        'status'       // Trạng thái (draft: Bản nháp, published: Đã xuất bản)
    ];
}