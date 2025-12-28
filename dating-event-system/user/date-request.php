<?php
require_once '../config/database.php';
require_once '../config/functions.php';

checkUserLogin();

$event_id = $_GET['event_id'] ?? 0;
$pdo = getDBConnection();

// 행사 정보
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}

// 사용자 정보
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

// 매칭되지 않은 이성 참석자
$stmt = $pdo->prepare("
    SELECT u.*
    FROM users u
    JOIN registrations r ON u.id = r.user_id
    JOIN checkins c ON r.id = c.registration_id
    WHERE r.event_id = ? AND u.gender != ? AND u.id != ?
    AND u.id NOT IN (
        SELECT user1_id FROM matches WHERE event_id = ? AND (user1_id = ? OR user2_id = ?)
        UNION
        SELECT user2_id FROM matches WHERE event_id = ? AND (user1_id = ? OR user2_id = ?)
    )
    ORDER BY u.name
");
$stmt->execute([
    $event_id, $current_user['gender'], $_SESSION['user_id'],
    $event_id, $_SESSION['user_id'], $_SESSION['user_id'],
    $event_id, $_SESSION['user_id'], $_SESSION['user_id']
]);
$available_users = $stmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'] ?? 0;
    $round = $_POST['round'] ?? 0;

    try {
        // 이미 신청했는지 확인
        $stmt = $pdo->prepare("
            SELECT * FROM date_requests
            WHERE event_id = ? AND requester_id = ? AND receiver_id = ?
        ");
        $stmt->execute([$event_id, $_SESSION['user_id'], $receiver_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $message = '이미 데이트를 신청한 상대입니다.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO date_requests (event_id, round, requester_id, receiver_id, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$event_id, $round, $_SESSION['user_id'], $receiver_id]);

            header('Location: matching.php?event_id=' . $event_id . '&success=1');
            exit;
        }
    } catch (Exception $e) {
        $message = '데이트 신청 중 오류가 발생했습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>데이트 신청 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💌 데이트 신청</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">행사 신청</a></li>
                    <li><a href="event-info.php">행사 안내</a></li>
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
            <h2>데이트 신청하기</h2>
            <p>매칭되지 않았지만 마음에 드는 분에게 데이트를 신청할 수 있습니다.</p>

            <?php if ($message): ?>
                <div class="alert alert-error"><?= escape($message) ?></div>
            <?php endif; ?>

            <?php if (empty($available_users)): ?>
                <div class="alert alert-info">
                    데이트를 신청할 수 있는 참석자가 없습니다.
                </div>
                <a href="matching.php?event_id=<?= $event_id ?>" class="btn btn-secondary">돌아가기</a>
            <?php else: ?>
                <div class="participant-grid">
                    <?php foreach ($available_users as $user): ?>
                        <div class="participant-card">
                            <?php if ($user['profile_image']): ?>
                                <img src="<?= escape($user['profile_image']) ?>" alt="프로필">
                            <?php else: ?>
                                <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; margin: 0 auto 15px;">
                                    <?= escape(mb_substr($user['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="participant-name"><?= escape($user['name']) ?></div>
                            <div class="participant-info">
                                <?= calculateAge($user['birth_date']) ?>세
                                <?php if ($user['height']): ?>
                                    | <?= $user['height'] ?>cm
                                <?php endif; ?>
                            </div>

                            <?php if ($user['occupation']): ?>
                                <div class="participant-info">💼 <?= escape($user['occupation']) ?></div>
                            <?php endif; ?>

                            <?php if ($user['education']): ?>
                                <div class="participant-info">🎓 <?= escape($user['education']) ?></div>
                            <?php endif; ?>

                            <form method="POST" style="margin-top: 15px;">
                                <input type="hidden" name="receiver_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="round" value="0">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    💌 데이트 신청
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
