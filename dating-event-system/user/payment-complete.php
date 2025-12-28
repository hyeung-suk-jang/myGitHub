<?php
require_once '../config/database.php';
require_once '../config/functions.php';

checkUserLogin();

$event_id = $_GET['event_id'] ?? 0;
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 완료 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>✅ 결제 완료</h1>
    </header>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 50px auto; text-align: center;">
            <div style="font-size: 80px; margin: 20px 0;">🎉</div>
            <h2>신청이 완료되었습니다!</h2>
            <p style="font-size: 18px; color: #666; margin: 20px 0;">
                <?= escape($event['title']) ?> 행사 신청이 성공적으로 완료되었습니다.
            </p>

            <div class="alert alert-success">
                행사 당일 체크인을 진행해주세요.<br>
                행사 안내 및 스케줄은 메뉴에서 확인하실 수 있습니다.
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <a href="event-info.php?event_id=<?= $event_id ?>" class="btn btn-primary" style="flex: 1;">행사 안내 보기</a>
                <a href="index.php" class="btn btn-secondary" style="flex: 1;">메인으로</a>
            </div>
        </div>
    </div>
</body>
</html>
