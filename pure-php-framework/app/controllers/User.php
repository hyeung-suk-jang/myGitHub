<?php
/**
 * 사용자 컨트롤러
 */

require_once CORE_PATH . '/controller.php';

class UserController extends Controller {
    /**
     * 사용자 목록
     */
    public function index() {
        $users = $this->db->selectAll('users');

        $this->view('users/index', [
            'title' => '사용자 목록',
            'users' => $users
        ]);
    }

    /**
     * 사용자 상세
     */
    public function show($id) {
        $user = $this->db->findById('users', $id);

        if (!$user) {
            $this->redirect('/users');
            return;
        }

        // 사용자의 게시글
        $posts = $this->db->query(
            "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$id]
        );

        $this->view('users/show', [
            'title' => $user['username'],
            'user' => $user,
            'posts' => $posts
        ]);
    }
}
