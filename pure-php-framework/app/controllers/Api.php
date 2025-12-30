<?php
/**
 * API 컨트롤러
 */

require_once CORE_PATH . '/controller.php';

class ApiController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->noLayout();
    }

    /**
     * 게시글 API
     */
    public function posts() {
        $page = max(1, intval(get('page', 1)));
        $limit = intval(get('limit', 10));
        $offset = ($page - 1) * $limit;

        $posts = $this->db->query(
            "SELECT p.*, u.username
             FROM posts p
             LEFT JOIN users u ON p.user_id = u.id
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        $this->json([
            'success' => true,
            'data' => $posts,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * 사용자 API
     */
    public function users() {
        $users = $this->db->query(
            "SELECT id, username, email, created_at FROM users"
        );

        $this->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
