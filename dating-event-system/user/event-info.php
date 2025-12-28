<?php
require_once '../config/database.php';
require_once '../config/functions.php';

checkUserLogin();

$pdo = getDBConnection();

// 사용자가 신청한 행사 목록
$stmt = $pdo->prepare("
    SELECT e.*, r.status as reg_status, r.payment_status
    FROM events e
    JOIN registrations r ON e.id = r.event_id
    WHERE r.user_id = ? AND r.payment_status = 'paid'
    ORDER BY e.event_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>행사 안내 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>📋 행사 안내</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">행사 신청</a></li>
                    <li><a href="event-info.php" class="active">행사 안내</a></li>
                    <li><a href="schedule.php">스케줄</a></li>
                    <li><a href="checkin.php">체크인</a></li>
                    <li><a href="participants.php">참석자</a></li>
                    <li><a href="selection.php">선택하기</a></li>
                    <li><a href="matching.php">매칭결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>내가 신청한 행사</h2>

            <?php if (empty($events)): ?>
                <div class="alert alert-info">
                    신청한 행사가 없습니다.
                </div>
                <a href="index.php" class="btn btn-primary">행사 둘러보기</a>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <h3 class="event-title"><?= escape($event['title']) ?></h3>

                        <div class="event-info">
                            <p>📅 <strong>일시:</strong> <?= formatDate($event['event_date'], 'Y년 m월 d일') ?> <?= date('H:i', strtotime($event['event_time'])) ?></p>
                            <p>📍 <strong>장소:</strong> <?= escape($event['location']) ?></p>
                            <?php if ($event['location_detail']): ?>
                                <p style="margin-left: 30px; color: #666;"><?= nl2br(escape($event['location_detail'])) ?></p>
                            <?php endif; ?>
                            <p>👥 <strong>모집인원:</strong> 남성 <?= $event['male_capacity'] ?>명 / 여성 <?= $event['female_capacity'] ?>명</p>
                        </div>

                        <?php if ($event['description']): ?>
                            <div style="margin: 15px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                                <strong>행사 소개:</strong><br>
                                <?= nl2br(escape($event['description'])) ?>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 15px;">
                            <span class="badge badge-success"><?= getRegistrationStatusText($event['reg_status']) ?></span>
                            <span class="badge badge-info"><?= getPaymentStatusText($event['payment_status']) ?></span>
                        </div>

                        <div style="margin-top: 15px;">
                            <a href="schedule.php?event_id=<?= $event['id'] ?>" class="btn btn-primary">스케줄 보기</a>
                            <a href="checkin.php?event_id=<?= $event['id'] ?>" class="btn btn-success">체크인</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
