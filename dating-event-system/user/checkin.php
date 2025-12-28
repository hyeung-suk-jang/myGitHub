<?php
require_once '../config/database.php';
require_once '../config/functions.php';

checkUserLogin();

$event_id = $_GET['event_id'] ?? 0;
$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT e.*, r.id as registration_id
    FROM events e
    JOIN registrations r ON e.id = r.event_id
    WHERE e.id = ? AND r.user_id = ? AND r.payment_status = 'paid'
");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}

// 체크인 여부 확인
$stmt = $pdo->prepare("SELECT * FROM checkins WHERE registration_id = ?");
$stmt->execute([$event['registration_id']]);
$checkin = $stmt->fetch();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$checkin) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO checkins (registration_id, checkin_location)
                VALUES (?, ?)
            ");
            $stmt->execute([$event['registration_id'], '행사장']);

            header('Location: checkin.php?event_id=' . $event_id . '&success=1');
            exit;
        } catch (Exception $e) {
            $message = '체크인 중 오류가 발생했습니다.';
        }
    }
}

$success = isset($_GET['success']) ? $_GET['success'] : false;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>체크인 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>✅ 체크인</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">행사 신청</a></li>
                    <li><a href="event-info.php">행사 안내</a></li>
                    <li><a href="schedule.php">스케줄</a></li>
                    <li><a href="checkin.php" class="active">체크인</a></li>
                    <li><a href="participants.php">참석자</a></li>
                    <li><a href="selection.php">선택하기</a></li>
                    <li><a href="matching.php">매칭결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 50px auto; text-align: center;">
            <h2><?= escape($event['title']) ?></h2>
            <p>📅 <?= formatDate($event['event_date'], 'Y년 m월 d일') ?> <?= date('H:i', strtotime($event['event_time'])) ?></p>
            <p>📍 <?= escape($event['location']) ?></p>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <div style="font-size: 60px; margin: 20px 0;">✅</div>
                    <h3>체크인이 완료되었습니다!</h3>
                    <p>행사에 참석해주셔서 감사합니다.</p>
                </div>

                <div style="margin-top: 30px;">
                    <a href="participants.php?event_id=<?= $event_id ?>" class="btn btn-primary">참석자 보기</a>
                    <a href="schedule.php?event_id=<?= $event_id ?>" class="btn btn-secondary">스케줄 보기</a>
                </div>

            <?php elseif ($checkin): ?>
                <div class="alert alert-info">
                    <div style="font-size: 60px; margin: 20px 0;">✅</div>
                    <h3>이미 체크인하셨습니다</h3>
                    <p>체크인 시간: <?= formatDate($checkin['checkin_time'], 'Y-m-d H:i:s') ?></p>
                </div>

                <div style="margin-top: 30px;">
                    <a href="participants.php?event_id=<?= $event_id ?>" class="btn btn-primary">참석자 보기</a>
                    <a href="schedule.php?event_id=<?= $event_id ?>" class="btn btn-secondary">스케줄 보기</a>
                </div>

            <?php else: ?>
                <?php if ($message): ?>
                    <div class="alert alert-error"><?= escape($message) ?></div>
                <?php endif; ?>

                <div style="margin: 30px 0;">
                    <div style="font-size: 80px; margin: 20px 0;">📱</div>
                    <h3>체크인을 진행해주세요</h3>
                    <p style="color: #666;">행사장에 도착하셨다면 아래 버튼을 눌러 체크인해주세요.</p>
                </div>

                <form method="POST">
                    <button type="submit" class="btn btn-primary btn-block" style="font-size: 18px; padding: 15px;">
                        지금 체크인하기
                    </button>
                </form>

                <div class="alert alert-info" style="margin-top: 20px; text-align: left;">
                    <strong>안내사항:</strong><br>
                    - 행사 시작 전에 체크인을 완료해주세요.<br>
                    - 체크인 완료 후 참석자 목록을 확인할 수 있습니다.<br>
                    - 체크인하신 분들만 선택 및 매칭에 참여할 수 있습니다.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
