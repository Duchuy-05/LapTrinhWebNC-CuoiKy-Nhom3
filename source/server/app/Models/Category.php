<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // Đổi sang lõi MongoDB

class Category extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';

    protected $fillable = [
        'name',        // Tên danh mục (VD: Lập trình Web)
        'slug',        // Đường dẫn chuẩn SEO (VD: lap-trinh-web)
        'description', // Mô tả danh mục
        'status'       // Trạng thái (1: Hiện, 0: Ẩn)
    ];
}