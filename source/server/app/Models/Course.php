<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // Lưu ý import đúng thư viện MongoDB

class Course extends Model
{
    // Chỉ định collection trong MongoDB
    protected $collection = 'courses';

    // Khai báo các field được phép lưu (Mass Assignment)
    protected $fillable = [
        'courseGroupId', // ID gốc để liên kết các version (Draft, Published...)
        'status',        // DRAFT, PUBLISHED, ARCHIVED, UNPUBLISHED
        'version',       // Phiên bản (1, 2, 3...)
        'title',         
        'description',   
        'thumbnail',     
        'tags',          
        'courseData',    // Array: Cấu trúc Unit/Lesson (Cột trái)
        'blocks',        // Array: Nội dung soạn thảo (Cột giữa)
        'authorId'       // ID giảng viên
    ];

    // Ép kiểu dữ liệu (Casting)
    protected $casts = [
        'courseData' => 'array',
        'blocks' => 'array',
        'tags' => 'string', // Có thể để array nếu bạn thiết kế tags dạng mảng
        'version' => 'integer'
    ];
}