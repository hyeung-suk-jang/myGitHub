<?php
require_once '../config/database.php';
require_once '../config/functions.php';

checkUserLogin();

$event_id = $_GET['event_id'] ?? 0;
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>스케줄 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>⏰ 행사 스케줄</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">행사 신청</a></li>
                    <li><a href="event-info.php">행사 안내</a></li>
                    <li><a href="schedule.php" class="active">스케줄</a></li>
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
            <h2><?= escape($event['title']) ?> 스케줄</h2>
            <p>📅 <?= formatDate($event['event_date'], 'Y년 m월 d일') ?> | 📍 <?= escape($event['location']) ?></p>

            <?php if ($event['schedule_detail']): ?>
                <div style="margin-top: 30px;">
                    <?= nl2br(escape($event['schedule_detail'])) ?>
                </div>
            <?php else: ?>
                <div style="margin-top: 30px;">
                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>📍 13:00 - 13:30 | 체크인 및 등록</h4>
                        <p>행사장 입구에서 체크인을 진행해주세요.</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>👋 13:30 - 14:00 | 오프닝 및 아이스 브레이킹</h4>
                        <p>간단한 게임과 함께 분위기를 풀어보는 시간</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>👥 14:00 - 15:00 | 조별 그룹 미팅</h4>
                        <p>소그룹으로 나누어 대화 나누는 시간</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>☕ 15:00 - 15:30 | 티타임</h4>
                        <p>자유롭게 대화를 나누는 시간</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>💝 15:30 - 16:00 | 1차 선택</h4>
                        <p>마음에 드는 상대 1, 2, 3지망 선택</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>💑 16:00 - 16:30 | 1차 매칭 및 데이트</h4>
                        <p>매칭된 커플 데이트 진행</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>💝 16:30 - 17:00 | 2차 선택</h4>
                        <p>마음에 드는 상대 1, 2, 3지망 선택</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>💑 17:00 - 17:30 | 2차 매칭 및 데이트</h4>
                        <p>매칭된 커플 데이트 진행</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>💝 17:30 - 18:00 | 3차 선택</h4>
                        <p>마지막 선택의 기회</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>💑 18:00 - 18:30 | 3차 매칭 및 데이트</h4>
                        <p>최종 매칭 커플 데이트</p>
                    </div>

                    <div style="border-left: 4px solid #667eea; padding: 15px; margin: 15px 0;">
                        <h4>🎉 18:30 - 19:00 | 마무리 및 폐회</h4>
                        <p>행사 마무리 및 인사</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="alert alert-info" style="margin-top: 30px;">
                <strong>안내사항:</strong><br>
                - 행사 시간은 상황에 따라 변동될 수 있습니다.<br>
                - 각 라운드의 선택은 관리자가 시작 버튼을 눌러야 진행됩니다.<br>
                - 매칭은 서로 선택한 경우에만 성사됩니다.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
