<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Core\Utilities\Validator;

class CommunityController
{
    private $postModel;
    private $commentModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->commentModel = new Comment();
    }

    public function index()
    {
        $category = $_GET['category'] ?? 'general';
        $page = $_GET['page'] ?? 1;

        $posts = $this->postModel->getPostsByCategory($category, $page);

        include __DIR__ . '/../Views/community/index.php';
    }

    public function show($id)
    {
        $post = $this->postModel->getPostWithAuthor($id);

        if (!$post) {
            http_response_code(404);
            echo "Post not found";
            return;
        }

        $comments = $this->commentModel->getCommentsByPost($id);

        include __DIR__ . '/../Views/community/show.php';
    }

    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Location: /login');
            return;
        }

        include __DIR__ . '/../Views/community/create.php';
    }

    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'category' => $_POST['category'] ?? 'general'
        ];

        $validator = Validator::make($data, [
            'title' => 'required|min:3|max:200',
            'content' => 'required|min:10'
        ]);

        if (!$validator->validate()) {
            http_response_code(422);
            echo json_encode(['errors' => $validator->errors()]);
            return;
        }

        $data['user_id'] = $_SESSION['user_id'];
        $data['status'] = 'published';
        $data['views'] = 0;

        $postId = $this->postModel->create($data);

        echo json_encode([
            'success' => true,
            'post_id' => $postId
        ]);
    }

    public function addComment($postId)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $content = $_POST['content'] ?? '';
        $parentId = $_POST['parent_id'] ?? null;

        $validator = Validator::make(['content' => $content], [
            'content' => 'required|min:2|max:1000'
        ]);

        if (!$validator->validate()) {
            http_response_code(422);
            echo json_encode(['errors' => $validator->errors()]);
            return;
        }

        $commentId = $this->commentModel->createComment($postId, $_SESSION['user_id'], $content, $parentId);

        echo json_encode([
            'success' => true,
            'comment_id' => $commentId
        ]);
    }
}
