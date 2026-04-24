<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Chuyển hướng thư mục lưu trữ sang /tmp của Vercel
$appStorage = '/tmp/storage';
$_ENV['APP_STORAGE'] = $appStorage;

// Tự động tạo các thư mục lõi mà Laravel cần để hoạt động và báo lỗi
$directories = [
    $appStorage . '/framework/cache/data',
    $appStorage . '/framework/sessions',
    $appStorage . '/framework/views',
    $appStorage . '/logs',
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

// Trỏ về file chạy gốc của Laravel
require __DIR__ . '/../public/index.php';
