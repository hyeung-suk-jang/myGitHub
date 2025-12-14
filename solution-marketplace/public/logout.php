<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
logoutUser();

Session::setFlash('success', '로그아웃되었습니다.');
redirect('/solution-marketplace/public/index.php');
