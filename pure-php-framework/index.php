<?php
/**
 * 순수 PHP 프레임워크 - 메인 진입점
 * Composer, Autoload 없이 순수하게 require/include 사용
 */

// 설정 파일 로드
require_once __DIR__ . '/config.php';

// 핵심 파일들 로드
require_once CORE_PATH . '/functions.php';
require_once CORE_PATH . '/database.php';
require_once CORE_PATH . '/router.php';
require_once CORE_PATH . '/template.php';

// 세션 시작
start_session();

// 라우터 생성
$router = new Router();

// ===================================
// 라우트 정의
// ===================================

// 홈페이지
$router->get('/', 'Home', 'index');

// 사용자 관련
$router->get('/users', 'User', 'index');
$router->get('/users/:id', 'User', 'show');
$router->get('/register', 'Auth', 'register');
$router->post('/register', 'Auth', 'doRegister');
$router->get('/login', 'Auth', 'login');
$router->post('/login', 'Auth', 'doLogin');
$router->get('/logout', 'Auth', 'logout');

// 게시판
$router->get('/posts', 'Post', 'index');
$router->get('/posts/create', 'Post', 'create');
$router->post('/posts/create', 'Post', 'store');
$router->get('/posts/:id', 'Post', 'show');
$router->get('/posts/:id/edit', 'Post', 'edit');
$router->post('/posts/:id/update', 'Post', 'update');
$router->post('/posts/:id/delete', 'Post', 'delete');

// API 예제
$router->get('/api/posts', 'Api', 'posts');
$router->get('/api/users', 'Api', 'users');

// 라우트 실행
$router->dispatch();
