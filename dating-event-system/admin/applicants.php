<?php
require_once '../config/database.php';
require_once '../config/functions.php';
checkAdminLogin();
$pdo = getDBConnection();

$event_id = $_GET['event_id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if ($event) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.*, r.status as reg_status, r.payment_status,
               c.checkin_time
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN checkins c ON c.registration_id = r.id
        WHERE r.event_id = ?
        ORDER BY r.registration_date DESC
    ");
    $stmt->execute([$event_id]);
    $applicants = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>신청자 관리</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <header>
        <h1>💼 신청자 관리</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">대시보드</a></li>
                    <li><a href="events.php">행사 관리</a></li>
                    <li><a href="applicants.php" class="active">신청자 관리</a></li>
                    <li><a href="groups.php">그룹 관리</a></li>
                    <li><a href="selections.php">선택 결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if ($event): ?>
            <div class="card">
                <h2><?= htmlspecialchars($event['title']) ?> - 신청자 목록</h2>
                <p>총 <?= count($applicants) ?>명 신청</p>
                <table>
                    <thead>
                        <tr>
                            <th>이름</th>
                            <th>성별</th>
                            <th>나이</th>
                            <th>연락처</th>
                            <th>직업</th>
                            <th>신청상태</th>
                            <th>결제상태</th>
                            <th>체크인</th>
                            <th>서류</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['name']) ?></td>
                            <td><?= $a['gender'] === 'M' ? '남' : '여' ?></td>
                            <td><?= calculateAge($a['birth_date']) ?>세</td>
                            <td><?= formatPhoneNumber($a['phone']) ?></td>
                            <td><?= htmlspecialchars($a['occupation'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $a['reg_status'] === 'approved' ? 'success' : 'warning' ?>"><?= getRegistrationStatusText($a['reg_status']) ?></span></td>
                            <td><span class="badge badge-<?= $a['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= getPaymentStatusText($a['payment_status']) ?></span></td>
                            <td><?= $a['checkin_time'] ? '<span class="badge badge-success">체크인</span>' : '<span class="badge badge-secondary">미체크인</span>' ?></td>
                            <td><a href="documents.php?user_id=<?= $a['id'] ?>&event_id=<?= $event_id ?>" class="btn btn-sm btn-info">보기</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">행사를 선택해주세요.</div>
            <a href="events.php" class="btn btn-primary">행사 목록으로</a>
        <?php endif; ?>
    </div>
</body>
</html>
