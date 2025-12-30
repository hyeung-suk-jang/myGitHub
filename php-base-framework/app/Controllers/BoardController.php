<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\Comment;

class BoardController extends CommunityController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function notice()
    {
        $page = $_GET['page'] ?? 1;
        $posts = $this->postModel->getPostsByCategory('notice', $page);
        $category = 'notice';

        include __DIR__ . '/../Views/board/notice.php';
    }

    public function faq()
    {
        $page = $_GET['page'] ?? 1;
        $posts = $this->postModel->getPostsByCategory('faq', $page);
        $category = 'faq';

        include __DIR__ . '/../Views/board/faq.php';
    }

    public function qna()
    {
        $page = $_GET['page'] ?? 1;
        $posts = $this->postModel->getPostsByCategory('qna', $page);
        $category = 'qna';

        include __DIR__ . '/../Views/board/qna.php';
    }
}
