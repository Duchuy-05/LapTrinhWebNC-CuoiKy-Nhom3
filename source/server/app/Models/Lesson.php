<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Lesson extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'lessons';

    protected $fillable = [
        'section_id',   // Thuộc chương nào
        'title',        // Tên bài giảng
        'video_url',    // Link video YouTube
        'document_url', // Link file tài liệu PDF (nếu có)
        'content',      // Nội dung text của bài giảng
        'order'         // Thứ tự bài giảng trong chương
    ];
}