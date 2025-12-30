<?php
/**
 * 게시글 컨트롤러
 */

require_once CORE_PATH . '/controller.php';

class PostController extends Controller {
    /**
     * 게시글 목록
     */
    public function index() {
        $page = max(1, intval(get('page', 1)));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        // 전체 게시글 수
        $total = $this->db->count('posts');

        // 페이지 수 계산
        $total_pages = ceil($total / $limit);

        // 게시글 조회
        $posts = $this->db->query(
            "SELECT p.*, u.username
             FROM posts p
             LEFT JOIN users u ON p.user_id = u.id
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        $this->view('posts/index', [
            'title' => '게시판',
            'posts' => $posts,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    /**
     * 게시글 상세
     */
    public function show($id) {
        $post = $this->db->queryOne(
            "SELECT p.*, u.username
             FROM posts p
             LEFT JOIN users u ON p.user_id = u.id
             WHERE p.id = ?",
            [$id]
        );

        if (!$post) {
            $this->redirect('/posts');
            return;
        }

        // 댓글 조회
        $comments = $this->db->query(
            "SELECT c.*, u.username
             FROM comments c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.post_id = ?
             ORDER BY c.created_at ASC",
            [$id]
        );

        $this->view('posts/show', [
            'title' => $post['title'],
            'post' => $post,
            'comments' => $comments
        ]);
    }

    /**
     * 게시글 작성 폼
     */
    public function create() {
        require_login();

        $this->view('posts/create', [
            'title' => '게시글 작성'
        ]);
    }

    /**
     * 게시글 저장
     */
    public function store() {
        require_login();

        if (!is_post()) {
            $this->redirect('/posts/create');
            return;
        }

        // CSRF 검증
        if (!csrf_verify(post('csrf_token'))) {
            $this->view('posts/create', [
                'title' => '게시글 작성',
                'error' => 'CSRF 토큰이 유효하지 않습니다.'
            ]);
            return;
        }

        $title = post('title');
        $content = post('content');

        // 유효성 검증
        $errors = [];

        if (empty($title)) {
            $errors[] = '제목을 입력하세요.';
        }

        if (empty($content)) {
            $errors[] = '내용을 입력하세요.';
        }

        if (!empty($errors)) {
            $this->view('posts/create', [
                'title' => '게시글 작성',
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }

        // 게시글 저장
        $post_id = $this->db->insertData('posts', [
            'user_id' => session_get('user_id'),
            'title' => $title,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($post_id) {
            $this->redirect('/posts/' . $post_id);
        } else {
            $this->view('posts/create', [
                'title' => '게시글 작성',
                'error' => '게시글 저장 중 오류가 발생했습니다.',
                'old' => $_POST
            ]);
        }
    }

    /**
     * 게시글 수정 폼
     */
    public function edit($id) {
        require_login();

        $post = $this->db->findById('posts', $id);

        if (!$post) {
            $this->redirect('/posts');
            return;
        }

        // 작성자 확인
        if ($post['user_id'] != session_get('user_id')) {
            $this->redirect('/posts/' . $id);
            return;
        }

        $this->view('posts/edit', [
            'title' => '게시글 수정',
            'post' => $post
        ]);
    }

    /**
     * 게시글 업데이트
     */
    public function update($id) {
        require_login();

        if (!is_post()) {
            $this->redirect('/posts/' . $id . '/edit');
            return;
        }

        $post = $this->db->findById('posts', $id);

        if (!$post || $post['user_id'] != session_get('user_id')) {
            $this->redirect('/posts');
            return;
        }

        // CSRF 검증
        if (!csrf_verify(post('csrf_token'))) {
            $this->view('posts/edit', [
                'title' => '게시글 수정',
                'error' => 'CSRF 토큰이 유효하지 않습니다.',
                'post' => $post
            ]);
            return;
        }

        $title = post('title');
        $content = post('content');

        // 업데이트
        $success = $this->db->updateData('posts', [
            'title' => $title,
            'content' => $content,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);

        if ($success) {
            $this->redirect('/posts/' . $id);
        } else {
            $this->view('posts/edit', [
                'title' => '게시글 수정',
                'error' => '게시글 수정 중 오류가 발생했습니다.',
                'post' => $post
            ]);
        }
    }

    /**
     * 게시글 삭제
     */
    public function delete($id) {
        require_login();

        $post = $this->db->findById('posts', $id);

        if ($post && $post['user_id'] == session_get('user_id')) {
            $this->db->deleteById('posts', $id);
        }

        $this->redirect('/posts');
    }
}
