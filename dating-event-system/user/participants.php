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

// 체크인한 참석자 목록 (성별 분리, 본인 제외)
$stmt = $pdo->prepare("
    SELECT u.*
    FROM users u
    JOIN registrations r ON u.id = r.user_id
    JOIN checkins c ON r.id = c.registration_id
    WHERE r.event_id = ? AND u.gender != ? AND u.id != ?
    ORDER BY u.name
");
$stmt->execute([$event_id, $current_user['gender'], $_SESSION['user_id']]);
$participants = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>참석자 목록 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>👥 참석자 목록</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">행사 신청</a></li>
                    <li><a href="event-info.php">행사 안내</a></li>
                    <li><a href="schedule.php">스케줄</a></li>
                    <li><a href="checkin.php">체크인</a></li>
                    <li><a href="participants.php" class="active">참석자</a></li>
                    <li><a href="selection.php">선택하기</a></li>
                    <li><a href="matching.php">매칭결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2><?= escape($event['title']) ?> 참석자</h2>
            <p>총 <?= count($participants) ?>명의 <?= getGenderText($current_user['gender'] === 'M' ? 'F' : 'M') ?> 참석자</p>

            <?php if (empty($participants)): ?>
                <div class="alert alert-info">
                    아직 체크인한 참석자가 없습니다.
                </div>
            <?php else: ?>
                <div class="participant-grid">
                    <?php foreach ($participants as $participant): ?>
                        <div class="participant-card">
                            <?php if ($participant['profile_image']): ?>
                                <img src="<?= escape($participant['profile_image']) ?>" alt="프로필">
                            <?php else: ?>
                                <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; margin: 0 auto 15px;">
                                    <?= escape(mb_substr($participant['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="participant-name"><?= escape($participant['name']) ?></div>
                            <div class="participant-info">
                                <?= calculateAge($participant['birth_date']) ?>세 |
                                <?php if ($participant['height']): ?>
                                    <?= $participant['height'] ?>cm
                                <?php endif; ?>
                            </div>

                            <?php if ($participant['occupation']): ?>
                                <div class="participant-info">💼 <?= escape($participant['occupation']) ?></div>
                            <?php endif; ?>

                            <?php if ($participant['education']): ?>
                                <div class="participant-info">🎓 <?= escape($participant['education']) ?></div>
                            <?php endif; ?>

                            <?php if ($participant['hobby']): ?>
                                <div class="participant-info" style="margin-top: 10px; font-size: 13px; color: #888;">
                                    <?= escape(mb_substr($participant['hobby'], 0, 50)) ?><?= mb_strlen($participant['hobby']) > 50 ? '...' : '' ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($participant['introduction']): ?>
                                <div class="participant-info" style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; font-size: 12px; text-align: left;">
                                    <?= escape(mb_substr($participant['introduction'], 0, 80)) ?><?= mb_strlen($participant['introduction']) > 80 ? '...' : '' ?>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 10px;">
                                <span class="badge badge-info">ID: <?= $participant['id'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="alert alert-info" style="margin-top: 30px;">
                <strong>안내:</strong> 선택 시간이 되면 마음에 드는 분을 선택하실 수 있습니다.<br>
                참석자 ID를 기억해두시면 선택 시 도움이 됩니다.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
