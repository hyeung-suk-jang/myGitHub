<?php

namespace App\Models;

use Core\Database\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = ['user_id', 'order_number', 'total_amount', 'status', 'payment_method', 'payment_id'];

    public function createOrder($userId, $items, $totalAmount)
    {
        $orderNumber = $this->generateOrderNumber();

        $orderId = $this->create([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'payment_method' => null,
            'payment_id' => null
        ]);

        return $orderId;
    }

    public function updatePaymentInfo($orderId, $paymentMethod, $paymentId)
    {
        return $this->update($orderId, [
            'payment_method' => $paymentMethod,
            'payment_id' => $paymentId,
            'status' => 'paid'
        ]);
    }

    public function getOrdersByUser($userId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $orders = $this->db->fetchAll($sql, [$userId, $perPage, $offset]);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = ?";
        $total = $this->db->fetchOne($countSql, [$userId])['total'];

        return [
            'data' => $orders,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    private function generateOrderNumber()
    {
        return 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -8));
    }
}
