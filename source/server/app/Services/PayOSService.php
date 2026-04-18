<?php

namespace App\Services;

use PayOS\PayOS;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use PayOS\Exceptions\APIException;
use PayOS\Exceptions\WebhookException;
use Exception;

class PayOSService
{
    private PayOS $payOS;

    public function __construct()
    {
        $clientId     = env('PAYOS_CLIENT_ID');
        $apiKey       = env('PAYOS_API_KEY');
        $checksumKey  = env('PAYOS_CHECKSUM_KEY');

        if (!$clientId || !$apiKey || !$checksumKey) {
            throw new Exception("Cấu hình PayOS chưa đầy đủ trong file .env");
        }

        // SDK v2 yêu cầu named parameters
        $this->payOS = new PayOS(
            clientId: $clientId,
            apiKey: $apiKey,
            checksumKey: $checksumKey,
        );
    }

    public function createPaymentLink($order): string
    {
        $domain = env('FRONTEND_URL', 'http://localhost:5173');

        // orderCode phải là số nguyên dương, dùng timestamp + id để tránh trùng
        $orderCode = (int) substr(str_replace('.', '', microtime(true)), 0, 10) * 10000 + rand(1000, 9999);

        if ($orderCode > 9007199254740991) {
            $orderCode = rand(100000000, 999999999);
        }

        // Lưu lại orderCode vào DB để webhook có thể map về đúng đơn hàng
        $order->update(['transaction_id' => $orderCode]);

        // SDK v2: dùng CreatePaymentLinkRequest model thay vì plain array
        $paymentData = new CreatePaymentLinkRequest(
            orderCode:   $orderCode,
            amount:      intval($order->price_paid),
            description: "Thanh toan khoa hoc", // tối đa 25 ký tự, không dấu
            returnUrl:   $domain . "/student/home",
            cancelUrl:   $domain . "/checkout/cancel",
        );

        try {
            // SDK v2: dùng paymentRequests->create() thay vì createPaymentLink()
            $result = $this->payOS->paymentRequests->create($paymentData);

            // SDK v2 trả về object → dùng -> thay vì ['key']
            return $result->checkoutUrl;

        } catch (APIException $e) {
            throw new Exception("Lỗi tạo link thanh toán PayOS: " . $e->getMessage());
        }
    }

    public function verifyWebhookData(array $webhookBody): object
    {
        try {
            // SDK v2: dùng webhooks->verify() thay vì verifyPaymentWebhookData()
            return $this->payOS->webhooks->verify($webhookBody);
        } catch (WebhookException $e) {
            throw new Exception("Webhook PayOS không hợp lệ: " . $e->getMessage());
        }
    }
}