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
        'authorId',      // ID giảng viên
        'price',         // Giá gốc - chỉ đặt khi soạn thảo, không thay đổi sau khi xuất bản
        'discountPrice', // Giá khuyến mãi (null = không KM, integer = giá thực học viên trả)
        'student_count', // Tổng số học viên đã mua
        'rating_count',  // Tổng số lượt đánh giá
        'rating_score',  // Điểm đánh giá trung bình (ví dụ: 4.8)
        'comments',
    ];

    // Ép kiểu dữ liệu (Casting)
    // Lưu ý: discountPrice được khai báo là integer nhưng MongoDB vẫn lưu null được.
    // Laravel/MongoDB driver sẽ chỉ cast khi giá trị khác null.
    protected $casts = [
        'courseData'    => 'array',
        'blocks'        => 'array',
        'tags'          => 'string',
        'version'       => 'integer',
        'price'         => 'integer',
        'discountPrice' => 'integer', // null-safe: chỉ cast khi không null
        'student_count' => 'integer',
        'rating_count'  => 'integer',
        'rating_score'  => 'float',
    ];

    /**
     * Thuộc tính tính toán: kiểm tra khóa học có đang khuyến mãi không.
     * Frontend dùng trường này để hiển thị badge "SALE".
     */
    protected $appends = ['is_on_sale'];

    public function getIsOnSaleAttribute(): bool
    {
        return $this->discountPrice !== null;
    }
}