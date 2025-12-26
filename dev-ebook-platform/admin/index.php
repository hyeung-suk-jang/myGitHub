<?php
require_once '../php/functions.php';

if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// 통계 데이터
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_ebooks' => $db->query("SELECT COUNT(*) FROM ebooks")->fetchColumn(),
    'total_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'completed'")->fetchColumn(),
    'total_revenue' => $db->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'completed'")->fetchColumn() ?: 0,
];

// 최근 주문
$recent_orders = $db->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();

// 최근 가입 회원
$recent_users = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 대시보드 - DevBooks</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .admin-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .admin-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th {
            background: var(--light-bg);
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        .admin-table td {
            padding: 15px;
            border-top: 1px solid var(--border-color);
        }
        .admin-nav {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }
        .admin-nav a {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background: var(--light-bg);
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .admin-nav a:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <div style="background: var(--dark-bg); padding: 15px 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="../index.php" style="color: white; text-decoration: none; font-weight: 600;">← 사이트로 돌아가기</a>
            <span style="color: white;">관리자: <?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-header">
            <h1 style="margin: 0; font-size: 2rem;">⚙️ 관리자 대시보드</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">DevBooks 관리 시스템</p>
        </div>

        <div class="admin-nav">
            <a href="index.php">📊 대시보드</a>
            <a href="ebooks.php">📚 전자책 관리</a>
            <a href="users.php">👥 회원 관리</a>
            <a href="orders.php">💳 주문 관리</a>
            <a href="categories.php">🏷️ 카테고리 관리</a>
        </div>

        <!-- 통계 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">👥 전체 회원</div>
                <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">📚 전자책</div>
                <div class="stat-value"><?= number_format($stats['total_ebooks']) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">💳 완료된 주문</div>
                <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">💰 총 매출</div>
                <div class="stat-value"><?= format_price($stats['total_revenue']) ?></div>
            </div>
        </div>

        <!-- 최근 주문 -->
        <div class="admin-table">
            <h2 style="padding: 20px; margin: 0; border-bottom: 2px solid var(--border-color);">최근 주문</h2>
            <table>
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>회원</th>
                        <th>금액</th>
                        <th>상태</th>
                        <th>주문일시</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                            <td><?= format_price($order['total_amount']) ?></td>
                            <td>
                                <span style="padding: 5px 12px; background: <?= $order['payment_status'] === 'completed' ? '#10b981' : '#f59e0b' ?>; color: white; border-radius: 12px; font-size: 0.85rem;">
                                    <?= $order['payment_status'] === 'completed' ? '완료' : '대기' ?>
                                </span>
                            </td>
                            <td><?= format_date($order['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 최근 가입 회원 -->
        <div class="admin-table">
            <h2 style="padding: 20px; margin: 0; border-bottom: 2px solid var(--border-color);">최근 가입 회원</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>이메일</th>
                        <th>사용자명</th>
                        <th>권한</th>
                        <th>가입일</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td>
                                <span style="padding: 5px 12px; background: <?= $user['role'] === 'admin' ? '#ef4444' : '#3b82f6' ?>; color: white; border-radius: 12px; font-size: 0.85rem;">
                                    <?= $user['role'] ?>
                                </span>
                            </td>
                            <td><?= format_date($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
