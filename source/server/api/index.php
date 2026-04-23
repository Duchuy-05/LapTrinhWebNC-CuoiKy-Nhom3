<?php
// 1. Ép PHP hiển thị mọi lỗi ẩn ra màn hình (thay vì lỗi 500 đen xì)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Chuyển hướng thư mục lưu trữ của Laravel sang /tmp
// (Vì /tmp là thư mục DUY NHẤT Vercel cho phép ghi đè dữ liệu)
$_ENV['APP_STORAGE'] = '/tmp/storage';

// 3. Trỏ đường dẫn về file chạy gốc của Laravel
require __DIR__ . '/../public/index.php';
