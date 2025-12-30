<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

// Authentication Routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', [CsrfMiddleware::class]);
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register', [CsrfMiddleware::class]);
$router->get('/logout', 'AuthController@logout');
$router->get('/verify-email', 'AuthController@verifyEmail');
$router->post('/forgot-password', 'AuthController@forgotPassword', [CsrfMiddleware::class]);
$router->post('/reset-password', 'AuthController@resetPassword', [CsrfMiddleware::class]);

// Social Authentication Routes
$router->get('/auth/{provider}', 'SocialAuthController@redirectToProvider');
$router->get('/auth/{provider}/callback', 'SocialAuthController@handleProviderCallback');

// Shop Routes
$router->get('/shop', 'ShopController@index');
$router->get('/shop/{id}', 'ShopController@show');
$router->get('/shop/category/{categoryId}', 'ShopController@category');
$router->get('/shop/search', 'ShopController@search');
$router->post('/shop/cart/add', 'ShopController@addToCart', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/shop/cart', 'ShopController@cart');
$router->get('/shop/checkout', 'ShopController@checkout', [AuthMiddleware::class]);

// Payment Routes
$router->post('/payment/create', 'PaymentController@createPayment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/payment/verify', 'PaymentController@verifyPayment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/payment/refund', 'PaymentController@refund', [AuthMiddleware::class, CsrfMiddleware::class]);

// Community Routes
$router->get('/community', 'CommunityController@index');
$router->get('/community/{id}', 'CommunityController@show');
$router->get('/community/create', 'CommunityController@create', [AuthMiddleware::class]);
$router->post('/community/store', 'CommunityController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/community/{id}/comment', 'CommunityController@addComment', [AuthMiddleware::class, CsrfMiddleware::class]);

// Board Routes
$router->get('/board/notice', 'BoardController@notice');
$router->get('/board/faq', 'BoardController@faq');
$router->get('/board/qna', 'BoardController@qna');

// Home Route
$router->get('/', function() {
    include __DIR__ . '/../app/Views/home.php';
});
