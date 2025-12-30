<?php

namespace App\Models;

use Core\Database\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $fillable = ['post_id', 'user_id', 'content', 'parent_id'];

    public function getCommentsByPost($postId)
    {
        $sql = "SELECT c.*, u.name as author_name
                FROM {$this->table} c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.post_id = ?
                ORDER BY c.created_at ASC";

        return $this->db->fetchAll($sql, [$postId]);
    }

    public function createComment($postId, $userId, $content, $parentId = null)
    {
        return $this->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content,
            'parent_id' => $parentId
        ]);
    }
}
