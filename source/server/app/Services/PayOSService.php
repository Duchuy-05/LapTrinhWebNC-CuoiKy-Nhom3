<?php

namespace App\Services;

use PayOS\PayOS;
use Exception;

class PayOSService {
    private $payOS;

    public function __construct() {
        $clientId = env('PAYOS_CLIENT_ID');
        $apiKey = env('PAYOS_API_KEY');
        $checksumKey = env('PAYOS_CHECKSUM_KEY');

        if (!$clientId || !$apiKey || !$checksumKey) {
            throw new Exception("Cấu hình PayOS chưa đầy đủ trong file .env");
        }

        $this->payOS = new PayOS($clientId, $apiKey, $checksumKey);
    }

    public function createPaymentLink($order) {
        $domain = "http://localhost:5173"; // Đổi thành domain thật khi lên production
        
        $orderCode = intval(date('ymdHis') . $order->id); 
        
        // Lưu lại orderCode vào database để sau này webhook gọi về còn biết đường mà map
        $order->update(['transaction_id' => $orderCode]);

        $data = [
            "orderCode" => $orderCode,
            "amount" => intval($order->price_paid),
            "description" => "Thanh toan khoa hoc", // Tối đa 25 ký tự, không dấu
            "returnUrl" => $domain . "/student/home", // Link redirect sau khi thanh toán xong
            "cancelUrl" => $domain . "/checkout/cancel" // Link redirect khi hủy
        ];

        try {
            $response = $this->payOS->createPaymentLink($data);
            return $response['checkoutUrl'];
        } catch (Exception $e) {
            throw new Exception("Lỗi tạo link thanh toán PayOS: " . $e->getMessage());
        }
    }

    public function verifyWebhookData($webhookBody) {
        return $this->payOS->verifyPaymentWebhookData($webhookBody);
    }
}