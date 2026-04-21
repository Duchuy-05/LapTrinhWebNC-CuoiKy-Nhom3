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

    public function createPaymentLink($order, string $source = 'web'): string
    {
        $domain = env('FRONTEND_URL', 'http://localhost:5173');
        $orderCode = (int) substr(str_replace('.', '', microtime(true)), 0, 10) * 10000 + rand(1000, 9999);
        if ($orderCode > 9007199254740991) {
            $orderCode = rand(100000000, 999999999);
        }
        $order->update(['transaction_id' => $orderCode]);
        return $this->buildPaymentLink($order, $orderCode, $domain, $source);
    }

    /**
     * Tạo lại link PayOS từ đơn PENDING đã có transaction_id (khi user bấm checkout lần 2).
     * Dùng lại orderCode cũ để webhook vẫn map đúng.
     */
    public function createPaymentLinkFromExisting($order, string $source = 'web' ): string
    {
        $domain = env('FRONTEND_URL', 'http://localhost:5173');
    return $this->buildPaymentLink($order, (int) $order->transaction_id, $domain, $source);
    }

    private function buildPaymentLink($order, int $orderCode, string $domain, string $source = 'web'): string
    {
        $appScheme = env('APP_SCHEME', 'studyhub');

        if ($source === 'app') {
            $returnUrl = "{$appScheme}://checkout/result?courseId={$order->course_id}&status=success";
            $cancelUrl = "{$appScheme}://checkout/result?courseId={$order->course_id}&status=cancel";
        } else {
            $returnUrl = $domain . "/student/courses/{$order->course_id}/checkout/result";
            $cancelUrl = $domain . "/student/courses/{$order->course_id}/checkout";
        }

        $paymentData = new CreatePaymentLinkRequest(
            orderCode:   $orderCode,
            amount:      intval($order->price_paid),
            description: "Thanh toan khoa hoc",
            returnUrl:   $returnUrl,
            cancelUrl:   $cancelUrl,
        );

        try {
            $result = $this->payOS->paymentRequests->create($paymentData);
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