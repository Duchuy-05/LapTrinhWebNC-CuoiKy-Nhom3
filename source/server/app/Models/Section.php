<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Section extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sections';

    protected $fillable = [
        'course_id', // Thuộc khóa học nào
        'title',     // Tên chương (VD: Chương 1: HTML Cơ bản)
        'order'      // Thứ tự sắp xếp của chương
    ];
}