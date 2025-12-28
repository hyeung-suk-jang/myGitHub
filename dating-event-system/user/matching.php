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

// 내 매칭 결과
$stmt = $pdo->prepare("
    SELECT m.*, u.*,  m.round, m.status as match_status
    FROM matches m
    JOIN users u ON (u.id = m.user1_id OR u.id = m.user2_id)
    WHERE m.event_id = ? AND (m.user1_id = ? OR m.user2_id = ?) AND u.id != ?
    ORDER BY m.round, m.match_time
");
$stmt->execute([$event_id, $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$matches = $stmt->fetchAll();

// 내가 받은 데이트 신청
$stmt = $pdo->prepare("
    SELECT dr.*, u.name, u.occupation, u.birth_date
    FROM date_requests dr
    JOIN users u ON u.id = dr.requester_id
    WHERE dr.event_id = ? AND dr.receiver_id = ? AND dr.status = 'pending'
    ORDER BY dr.request_time DESC
");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$received_requests = $stmt->fetchAll();

// 데이트 신청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if ($request_id && ($action === 'accept' || $action === 'reject')) {
        try {
            $status = $action === 'accept' ? 'accepted' : 'rejected';
            $stmt = $pdo->prepare("
                UPDATE date_requests
                SET status = ?, response_time = NOW()
                WHERE id = ? AND receiver_id = ?
            ");
            $stmt->execute([$status, $request_id, $_SESSION['user_id']]);

            header('Location: matching.php?event_id=' . $event_id);
            exit;
        } catch (Exception $e) {
            $error = '처리 중 오류가 발생했습니다.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>매칭 결과 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💑 매칭 결과</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">행사 신청</a></li>
                    <li><a href="event-info.php">행사 안내</a></li>
                    <li><a href="schedule.php">스케줄</a></li>
                    <li><a href="checkin.php">체크인</a></li>
                    <li><a href="participants.php">참석자</a></li>
                    <li><a href="selection.php">선택하기</a></li>
                    <li><a href="matching.php" class="active">매칭결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- 매칭 결과 -->
        <div class="card">
            <h2>🎉 내 매칭 결과</h2>

            <?php if (empty($matches)): ?>
                <div class="alert alert-info">
                    아직 매칭된 상대가 없습니다.<br>
                    선택 시간에 마음에 드는 분을 선택해주세요!
                </div>
            <?php else: ?>
                <?php foreach ($matches as $match): ?>
                    <div class="match-card">
                        <h3>✨ <?= $match['round'] ?>차 매칭 성공!</h3>
                        <div style="font-size: 24px; margin: 20px 0;">
                            <?= escape($match['name']) ?>님과 매칭되었습니다
                        </div>
                        <div style="font-size: 16px; opacity: 0.9;">
                            <?= calculateAge($match['birth_date']) ?>세 |
                            <?= escape($match['occupation'] ?? '직업미상') ?>
                        </div>
                        <div style="margin-top: 20px; font-size: 14px; opacity: 0.8;">
                            매칭 시간: <?= formatDate($match['match_time'], 'Y-m-d H:i') ?>
                        </div>
                        <div style="margin-top: 20px;">
                            <span class="badge" style="background: white; color: #667eea; font-size: 14px;">
                                <?= $match['match_status'] === 'matched' ? '매칭됨' : ($match['match_status'] === 'dating' ? '데이트 중' : '완료') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 받은 데이트 신청 -->
        <?php if (!empty($received_requests)): ?>
            <div class="card">
                <h2>💌 받은 데이트 신청</h2>

                <?php foreach ($received_requests as $request): ?>
                    <div style="border: 2px solid #667eea; border-radius: 10px; padding: 20px; margin: 15px 0; background-color: #f8f9fa;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; color: #667eea;">
                                    <?= escape($request['name']) ?>님의 데이트 신청
                                </h3>
                                <p style="margin: 10px 0 0 0; color: #666;">
                                    <?= calculateAge($request['birth_date']) ?>세 |
                                    <?= escape($request['occupation'] ?? '직업미상') ?> |
                                    <?= $request['round'] ?>차 선택 후
                                </p>
                                <p style="margin: 5px 0 0 0; font-size: 13px; color: #999;">
                                    신청 시간: <?= formatDate($request['request_time'], 'Y-m-d H:i') ?>
                                </p>
                            </div>
                            <div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-success">수락</button>
                                </form>
                                <form method="POST" style="display: inline; margin-left: 10px;">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger">거절</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- 데이트 신청하기 (매칭되지 않은 경우) -->
        <div class="card">
            <h2>💝 데이트 신청하기</h2>
            <p>매칭되지 않았지만 마음에 드는 분이 있다면 데이트를 신청할 수 있습니다.</p>
            <a href="date-request.php?event_id=<?= $event_id ?>" class="btn btn-primary">데이트 신청하기</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
