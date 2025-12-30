<?php

namespace Core\Utilities;

class Payment
{
    private $config;
    private $gateway;

    public function __construct($gateway, $config)
    {
        $this->gateway = $gateway;
        $this->config = $config[$gateway] ?? [];
    }

    public function createPayment($data)
    {
        switch ($this->gateway) {
            case 'toss':
                return $this->createTossPayment($data);
            case 'iamport':
                return $this->createIamportPayment($data);
            case 'stripe':
                return $this->createStripePayment($data);
            default:
                throw new \Exception("Unsupported payment gateway: {$this->gateway}");
        }
    }

    private function createTossPayment($data)
    {
        $params = [
            'amount' => $data['amount'],
            'orderId' => $data['order_id'],
            'orderName' => $data['order_name'],
            'customerName' => $data['customer_name'] ?? '',
            'successUrl' => $this->config['success_url'],
            'failUrl' => $this->config['fail_url']
        ];

        return [
            'payment_key' => bin2hex(random_bytes(16)),
            'checkout_url' => $this->config['checkout_url'],
            'params' => $params
        ];
    }

    private function createIamportPayment($data)
    {
        $params = [
            'merchant_uid' => $data['order_id'],
            'name' => $data['order_name'],
            'amount' => $data['amount'],
            'buyer_name' => $data['customer_name'] ?? '',
            'buyer_email' => $data['customer_email'] ?? '',
            'buyer_tel' => $data['customer_phone'] ?? ''
        ];

        return [
            'imp_uid' => 'imp_' . uniqid(),
            'merchant_uid' => $data['order_id'],
            'params' => $params
        ];
    }

    private function createStripePayment($data)
    {
        $ch = curl_init('https://api.stripe.com/v1/payment_intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'krw',
            'description' => $data['order_name']
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->config['secret_key']
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function verifyPayment($paymentId, $data = [])
    {
        switch ($this->gateway) {
            case 'toss':
                return $this->verifyTossPayment($paymentId, $data);
            case 'iamport':
                return $this->verifyIamportPayment($paymentId);
            case 'stripe':
                return $this->verifyStripePayment($paymentId);
            default:
                throw new \Exception("Unsupported payment gateway: {$this->gateway}");
        }
    }

    private function verifyTossPayment($paymentKey, $data)
    {
        $ch = curl_init("https://api.tosspayments.com/v1/payments/confirm");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'paymentKey' => $paymentKey,
            'orderId' => $data['order_id'],
            'amount' => $data['amount']
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->config['secret_key'] . ':'),
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        }

        return false;
    }

    private function verifyIamportPayment($impUid)
    {
        $token = $this->getIamportToken();

        $ch = curl_init("https://api.iamport.kr/payments/{$impUid}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        return $result['response'] ?? false;
    }

    private function getIamportToken()
    {
        $ch = curl_init('https://api.iamport.kr/users/getToken');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'imp_key' => $this->config['api_key'],
            'imp_secret' => $this->config['secret_key']
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        return $result['response']['access_token'] ?? null;
    }

    private function verifyStripePayment($paymentIntentId)
    {
        $ch = curl_init("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->config['secret_key']
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function refund($paymentId, $amount = null)
    {
        switch ($this->gateway) {
            case 'toss':
                return $this->refundTossPayment($paymentId, $amount);
            case 'iamport':
                return $this->refundIamportPayment($paymentId, $amount);
            case 'stripe':
                return $this->refundStripePayment($paymentId, $amount);
            default:
                throw new \Exception("Unsupported payment gateway: {$this->gateway}");
        }
    }

    private function refundTossPayment($paymentKey, $amount)
    {
        $ch = curl_init("https://api.tosspayments.com/v1/payments/{$paymentKey}/cancel");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'cancelReason' => 'Customer request',
            'cancelAmount' => $amount
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->config['secret_key'] . ':'),
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function refundIamportPayment($impUid, $amount)
    {
        $token = $this->getIamportToken();

        $ch = curl_init('https://api.iamport.kr/payments/cancel');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'imp_uid' => $impUid,
            'amount' => $amount,
            'reason' => 'Customer request'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function refundStripePayment($paymentIntentId, $amount)
    {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amount) {
            $params['amount'] = $amount;
        }

        $ch = curl_init('https://api.stripe.com/v1/refunds');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->config['secret_key']
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
