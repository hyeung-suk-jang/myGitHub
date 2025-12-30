<?php

namespace App\Models;

use Core\Database\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['user_id', 'title', 'content', 'category', 'views', 'status'];

    public function getPostsByCategory($category, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, u.name as author_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.category = ? AND p.status = 'published'
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $posts = $this->db->fetchAll($sql, [$category, $perPage, $offset]);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE category = ? AND status = 'published'";
        $total = $this->db->fetchOne($countSql, [$category])['total'];

        return [
            'data' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function getPostWithAuthor($id)
    {
        $sql = "SELECT p.*, u.name as author_name, u.email as author_email
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = ? AND p.status = 'published'";

        $post = $this->db->fetchOne($sql, [$id]);

        if ($post) {
            $this->incrementViews($id);
        }

        return $post;
    }

    public function incrementViews($id)
    {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function search($keyword, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%{$keyword}%";

        $sql = "SELECT p.*, u.name as author_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE (p.title LIKE ? OR p.content LIKE ?) AND p.status = 'published'
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $posts = $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $perPage, $offset]);

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE (title LIKE ? OR content LIKE ?) AND status = 'published'";
        $total = $this->db->fetchOne($countSql, [$searchTerm, $searchTerm])['total'];

        return [
            'data' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
