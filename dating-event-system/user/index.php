<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// 진행 중인 행사 목록 가져오기
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT * FROM events
    WHERE status IN ('upcoming', 'registration_open')
    AND registration_start_date <= NOW()
    AND registration_end_date >= NOW()
    ORDER BY event_date ASC
");
$stmt->execute();
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>미팅 행사 신청 - 결혼정보 회사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💑 결혼정보 미팅 행사</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php" class="active">행사 신청</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="event-info.php">행사 안내</a></li>
                        <li><a href="schedule.php">스케줄</a></li>
                        <li><a href="checkin.php">체크인</a></li>
                        <li><a href="participants.php">참석자</a></li>
                        <li><a href="selection.php">선택하기</a></li>
                        <li><a href="matching.php">매칭결과</a></li>
                        <li><a href="logout.php">로그아웃</a></li>
                    <?php else: ?>
                        <li><a href="login.php">로그인</a></li>
                        <li><a href="register.php">회원가입</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>🎉 진행 중인 미팅 행사</h2>
            <p>결혼정보 회사에서 준비한 특별한 만남의 기회입니다. 아래 행사 중 원하시는 행사에 신청해주세요.</p>
        </div>

        <?php if (empty($events)): ?>
            <div class="card">
                <div class="alert alert-info">
                    현재 신청 가능한 행사가 없습니다.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <h3 class="event-title"><?= escape($event['title']) ?></h3>
                    <div class="event-info">
                        <p>📅 <strong>일시:</strong> <?= formatDate($event['event_date'], 'Y년 m월 d일') ?> <?= date('H:i', strtotime($event['event_time'])) ?></p>
                        <p>📍 <strong>장소:</strong> <?= escape($event['location']) ?></p>
                        <p>👥 <strong>모집인원:</strong> 남성 <?= $event['male_capacity'] ?>명 / 여성 <?= $event['female_capacity'] ?>명</p>
                        <p>💰 <strong>참가비:</strong> <?= formatMoney($event['registration_fee']) ?></p>
                        <p>⏰ <strong>신청기간:</strong>
                            <?= formatDate($event['registration_start_date'], 'Y-m-d H:i') ?> ~
                            <?= formatDate($event['registration_end_date'], 'Y-m-d H:i') ?>
                        </p>
                    </div>

                    <?php if (!empty($event['description'])): ?>
                        <div style="margin: 15px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                            <strong>행사 소개:</strong><br>
                            <?= nl2br(escape($event['description'])) ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    // 이미 신청했는지 확인
                    if (isset($_SESSION['user_id'])) {
                        $stmt = $pdo->prepare("
                            SELECT * FROM registrations
                            WHERE event_id = ? AND user_id = ?
                        ");
                        $stmt->execute([$event['id'], $_SESSION['user_id']]);
                        $registration = $stmt->fetch();

                        if ($registration) {
                            echo '<div class="alert alert-success">
                                이미 신청하셨습니다.
                                <strong>상태:</strong> ' . getRegistrationStatusText($registration['status']) . ' |
                                <strong>결제:</strong> ' . getPaymentStatusText($registration['payment_status']) . '
                            </div>';
                            if ($registration['payment_status'] === 'unpaid') {
                                echo '<a href="payment.php?event_id=' . $event['id'] . '" class="btn btn-primary">결제하기</a>';
                            }
                        } else {
                            echo '<a href="apply.php?event_id=' . $event['id'] . '" class="btn btn-primary">이 행사 신청하기</a>';
                        }
                    } else {
                        echo '<a href="login.php?redirect=apply.php?event_id=' . $event['id'] . '" class="btn btn-primary">로그인 후 신청하기</a>';
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/common.js"></script>
</body>
</html>
