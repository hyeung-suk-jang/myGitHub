<?php

namespace App\Controllers;

use App\Models\Order;
use Core\Utilities\Payment;

class PaymentController
{
    private $orderModel;
    private $paymentConfig;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->paymentConfig = require __DIR__ . '/../../config/payment.php';
    }

    public function createPayment()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $gateway = $data['gateway'] ?? 'toss';
        $amount = $data['amount'] ?? 0;
        $orderName = $data['order_name'] ?? 'Order';

        $orderId = $this->orderModel->createOrder($userId, [], $amount);
        $order = $this->orderModel->find($orderId);

        $payment = new Payment($gateway, $this->paymentConfig);

        try {
            $paymentData = $payment->createPayment([
                'order_id' => $order['order_number'],
                'order_name' => $orderName,
                'amount' => $amount,
                'customer_name' => $_SESSION['user_name'] ?? '',
                'customer_email' => $_SESSION['user_email'] ?? ''
            ]);

            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'payment_data' => $paymentData
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Payment creation failed']);
        }
    }

    public function verifyPayment()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $gateway = $data['gateway'] ?? 'toss';
        $paymentId = $data['payment_id'] ?? '';
        $orderId = $data['order_id'] ?? '';

        $order = $this->orderModel->find($orderId);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        $payment = new Payment($gateway, $this->paymentConfig);

        try {
            $verificationData = $payment->verifyPayment($paymentId, [
                'order_id' => $order['order_number'],
                'amount' => $order['total_amount']
            ]);

            if ($verificationData) {
                $this->orderModel->updatePaymentInfo($orderId, $gateway, $paymentId);

                echo json_encode([
                    'success' => true,
                    'message' => 'Payment verified successfully'
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Payment verification failed']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Payment verification error']);
        }
    }

    public function refund()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = $data['order_id'] ?? '';
        $amount = $data['amount'] ?? null;

        $order = $this->orderModel->find($orderId);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        $payment = new Payment($order['payment_method'], $this->paymentConfig);

        try {
            $refundData = $payment->refund($order['payment_id'], $amount);

            if ($refundData) {
                $this->orderModel->update($orderId, ['status' => 'refunded']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Refund processed successfully'
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Refund failed']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Refund processing error']);
        }
    }
}
