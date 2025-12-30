<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Order;

class ShopController
{
    private $productModel;
    private $orderModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->orderModel = new Order();
    }

    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $products = $this->productModel->getActiveProducts($page);

        include __DIR__ . '/../Views/shop/index.php';
    }

    public function show($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            http_response_code(404);
            echo "Product not found";
            return;
        }

        include __DIR__ . '/../Views/shop/show.php';
    }

    public function category($categoryId)
    {
        $page = $_GET['page'] ?? 1;
        $products = $this->productModel->getProductsByCategory($categoryId, $page);

        include __DIR__ . '/../Views/shop/category.php';
    }

    public function search()
    {
        $keyword = $_GET['q'] ?? '';
        $page = $_GET['page'] ?? 1;

        $products = $this->productModel->search($keyword, $page);

        include __DIR__ . '/../Views/shop/search.php';
    }

    public function addToCart()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;

        $product = $this->productModel->find($productId);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'product' => $product,
                'quantity' => $quantity
            ];
        }

        echo json_encode([
            'success' => true,
            'cart_count' => count($_SESSION['cart'])
        ]);
    }

    public function cart()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cart = $_SESSION['cart'] ?? [];

        include __DIR__ . '/../Views/shop/cart.php';
    }

    public function checkout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            header('Location: /login');
            return;
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            http_response_code(400);
            echo "Cart is empty";
            return;
        }

        include __DIR__ . '/../Views/shop/checkout.php';
    }
}
