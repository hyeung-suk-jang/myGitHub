<?php
require_once '../config/database.php';
require_once '../config/functions.php';
checkAdminLogin();

$pdo = getDBConnection();

// 통계
$stmt = $pdo->query("SELECT COUNT(*) FROM events");
$total_events = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM registrations WHERE payment_status = 'paid'");
$total_registrations = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(e.registration_fee) FROM registrations r JOIN events e ON r.event_id = e.id WHERE r.payment_status = 'paid'");
$total_revenue = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 5");
$recent_events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 대시보드</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <header>
        <h1>💼 미팅 행사 관리 시스템</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php" class="active">대시보드</a></li>
                    <li><a href="events.php">행사 관리</a></li>
                    <li><a href="applicants.php">신청자 관리</a></li>
                    <li><a href="groups.php">그룹 관리</a></li>
                    <li><a href="selections.php">선택 결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>📊 통계</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>총 행사 수</h3>
                    <div class="number"><?= $total_events ?></div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                    <h3>총 회원 수</h3>
                    <div class="number"><?= $total_users ?></div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);">
                    <h3>총 신청 수</h3>
                    <div class="number"><?= $total_registrations ?></div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                    <h3>총 매출</h3>
                    <div class="number"><?= number_format($total_revenue) ?>원</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>🎉 최근 행사</h2>
            <?php if (empty($recent_events)): ?>
                <p>등록된 행사가 없습니다.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>행사명</th>
                            <th>일시</th>
                            <th>장소</th>
                            <th>상태</th>
                            <th>참가비</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= $event['event_date'] ?> <?= substr($event['event_time'], 0, 5) ?></td>
                            <td><?= htmlspecialchars($event['location']) ?></td>
                            <td><span class="badge badge-info"><?= $event['status'] ?></span></td>
                            <td><?= number_format($event['registration_fee']) ?>원</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
