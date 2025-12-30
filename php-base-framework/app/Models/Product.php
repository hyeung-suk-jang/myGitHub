<?php

namespace App\Models;

use Core\Database\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['name', 'description', 'price', 'stock', 'category_id', 'image_url', 'status'];

    public function getActiveProducts($page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $products = $this->db->fetchAll($sql, [$perPage, $offset]);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'active'";
        $total = $this->db->fetchOne($countSql)['total'];

        return [
            'data' => $products,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function getProductsByCategory($categoryId, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $products = $this->db->fetchAll($sql, [$categoryId, $perPage, $offset]);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE category_id = ? AND status = 'active'";
        $total = $this->db->fetchOne($countSql, [$categoryId])['total'];

        return [
            'data' => $products,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function search($keyword, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%{$keyword}%";

        $sql = "SELECT * FROM {$this->table} WHERE (name LIKE ? OR description LIKE ?) AND status = 'active' ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $products = $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $perPage, $offset]);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE (name LIKE ? OR description LIKE ?) AND status = 'active'";
        $total = $this->db->fetchOne($countSql, [$searchTerm, $searchTerm])['total'];

        return [
            'data' => $products,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function updateStock($productId, $quantity)
    {
        $product = $this->find($productId);

        if (!$product) {
            return false;
        }

        $newStock = $product['stock'] - $quantity;

        if ($newStock < 0) {
            return false;
        }

        return $this->update($productId, ['stock' => $newStock]);
    }
}
