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

// 신청 정보
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE event_id = ? AND user_id = ?");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$registration = $stmt->fetch();

if (!$event || !$registration) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // 결제 정보 저장
        $payment_method = $_POST['payment_method'];
        $transaction_id = 'TXN' . time() . rand(1000, 9999);

        $stmt = $pdo->prepare("
            INSERT INTO payments (registration_id, amount, payment_method, transaction_id, status)
            VALUES (?, ?, ?, ?, 'completed')
        ");
        $stmt->execute([
            $registration['id'],
            $event['registration_fee'],
            $payment_method,
            $transaction_id
        ]);

        // 신청 상태 업데이트
        $stmt = $pdo->prepare("
            UPDATE registrations
            SET payment_status = 'paid', status = 'approved'
            WHERE id = ?
        ");
        $stmt->execute([$registration['id']]);

        $pdo->commit();

        header('Location: payment-complete.php?event_id=' . $event_id);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = '결제 처리 중 오류가 발생했습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💳 결제</h1>
    </header>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 50px auto;">
            <h2>참가비 결제</h2>

            <div class="event-info">
                <h3><?= escape($event['title']) ?></h3>
                <p>📅 <?= formatDate($event['event_date'], 'Y년 m월 d일') ?> <?= date('H:i', strtotime($event['event_time'])) ?></p>
                <p>📍 <?= escape($event['location']) ?></p>
            </div>

            <hr style="margin: 20px 0;">

            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; margin: 10px 0;">
                    <span>참가비</span>
                    <strong><?= formatMoney($event['registration_fee']) ?></strong>
                </div>
                <hr>
                <div style="display: flex; justify-content: space-between; margin: 10px 0; font-size: 20px;">
                    <strong>총 결제금액</strong>
                    <strong style="color: #667eea;"><?= formatMoney($event['registration_fee']) ?></strong>
                </div>
            </div>

            <?php if ($registration['payment_status'] === 'paid'): ?>
                <div class="alert alert-success">
                    이미 결제가 완료되었습니다.
                </div>
                <a href="event-info.php" class="btn btn-primary btn-block">행사 정보 보기</a>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>결제 방법</label>
                        <select name="payment_method" required>
                            <option value="">선택하세요</option>
                            <option value="card">신용카드</option>
                            <option value="bank_transfer">계좌이체</option>
                            <option value="kakao_pay">카카오페이</option>
                            <option value="naver_pay">네이버페이</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <strong>안내:</strong> 실제 결제 연동이 필요합니다. 현재는 테스트 모드로 결제가 완료 처리됩니다.
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">결제하기</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
