<?php
require_once '../config/database.php';
require_once '../config/functions.php';

checkUserLogin();

$event_id = $_GET['event_id'] ?? 0;
$pdo = getDBConnection();

// 행사 정보 가져오기
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}

// 이미 신청했는지 확인
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE event_id = ? AND user_id = ?");
$stmt->execute([$event_id, $_SESSION['user_id']]);
$existing = $stmt->fetch();

if ($existing) {
    header('Location: payment.php?event_id=' . $event_id);
    exit;
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($step === 1) {
            // Step 1: 행사 신청
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO registrations (event_id, user_id, status, payment_status)
                VALUES (?, ?, 'pending', 'unpaid')
            ");
            $stmt->execute([$event_id, $_SESSION['user_id']]);
            $registration_id = $pdo->lastInsertId();

            $pdo->commit();

            header('Location: apply.php?event_id=' . $event_id . '&step=2');
            exit;

        } elseif ($step === 2) {
            // Step 2: 서류 업로드
            $required_docs = ['id_card', 'salary_statement', 'residence_certificate', 'family_certificate'];
            $upload_errors = [];

            foreach ($required_docs as $doc_type) {
                if (isset($_FILES[$doc_type]) && $_FILES[$doc_type]['error'] === UPLOAD_ERR_OK) {
                    $result = uploadFile($_FILES[$doc_type], $_SESSION['user_id'], $event_id, $doc_type);

                    if ($result['success']) {
                        $stmt = $pdo->prepare("
                            INSERT INTO documents (user_id, event_id, document_type, file_name, file_path, file_size)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $_SESSION['user_id'],
                            $event_id,
                            $doc_type,
                            $result['file_name'],
                            $result['file_path'],
                            $result['file_size']
                        ]);
                    } else {
                        $upload_errors[] = getDocumentTypeText($doc_type) . ': ' . $result['message'];
                    }
                }
            }

            if (empty($upload_errors)) {
                header('Location: payment.php?event_id=' . $event_id);
                exit;
            } else {
                $error = '일부 서류 업로드에 실패했습니다:<br>' . implode('<br>', $upload_errors);
            }
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = '신청 중 오류가 발생했습니다: ' . $e->getMessage();
    }
}

// 업로드된 서류 확인
$stmt = $pdo->prepare("SELECT document_type FROM documents WHERE user_id = ? AND event_id = ?");
$stmt->execute([$_SESSION['user_id'], $event_id]);
$uploaded_docs = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>행사 신청 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💑 행사 신청</h1>
    </header>

    <div class="container">
        <div class="card">
            <h2><?= escape($event['title']) ?> 신청</h2>

            <!-- 진행 단계 -->
            <div style="display: flex; justify-content: center; margin: 30px 0;">
                <div style="text-align: center; margin: 0 20px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: <?= $step >= 1 ? '#667eea' : '#ddd' ?>; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">1</div>
                    <div>신청 확인</div>
                </div>
                <div style="text-align: center; margin: 0 20px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: <?= $step >= 2 ? '#667eea' : '#ddd' ?>; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">2</div>
                    <div>서류 제출</div>
                </div>
                <div style="text-align: center; margin: 0 20px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: <?= $step >= 3 ? '#667eea' : '#ddd' ?>; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">3</div>
                    <div>결제</div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: 신청 확인 -->
                <div class="event-info">
                    <p>📅 <strong>일시:</strong> <?= formatDate($event['event_date'], 'Y년 m월 d일') ?> <?= date('H:i', strtotime($event['event_time'])) ?></p>
                    <p>📍 <strong>장소:</strong> <?= escape($event['location']) ?></p>
                    <p>💰 <strong>참가비:</strong> <?= formatMoney($event['registration_fee']) ?></p>
                </div>

                <div class="alert alert-info" style="margin-top: 20px;">
                    <strong>안내사항:</strong><br>
                    - 다음 단계에서 필수 서류를 제출하셔야 합니다.<br>
                    - 필수 서류: 신분증, 3개월 급여통장 내역, 주민등록등본, 가족관계증명서<br>
                    - 서류 제출 후 참가비 결제를 진행합니다.<br>
                    - 허위 정보 제공 시 참가가 취소될 수 있습니다.
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" required>
                            위 안내사항을 확인했으며, 정확한 정보를 제공하는 것에 동의합니다.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">다음 단계로</button>
                </form>

            <?php elseif ($step === 2): ?>
                <!-- Step 2: 서류 업로드 -->
                <h3>필수 서류 제출</h3>
                <p>아래 서류를 업로드해주세요. (JPG, PNG, PDF 파일만 가능, 최대 5MB)</p>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="id_card">
                            신분증 <?= in_array('id_card', $uploaded_docs) ? '<span class="badge badge-success">✓ 업로드 완료</span>' : '<span style="color: red;">*</span>' ?>
                        </label>
                        <input type="file" id="id_card" name="id_card" accept=".jpg,.jpeg,.png,.pdf" <?= !in_array('id_card', $uploaded_docs) ? 'required' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label for="salary_statement">
                            3개월 급여통장 내역 <?= in_array('salary_statement', $uploaded_docs) ? '<span class="badge badge-success">✓ 업로드 완료</span>' : '<span style="color: red;">*</span>' ?>
                        </label>
                        <input type="file" id="salary_statement" name="salary_statement" accept=".jpg,.jpeg,.png,.pdf" <?= !in_array('salary_statement', $uploaded_docs) ? 'required' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label for="residence_certificate">
                            주민등록등본 <?= in_array('residence_certificate', $uploaded_docs) ? '<span class="badge badge-success">✓ 업로드 완료</span>' : '<span style="color: red;">*</span>' ?>
                        </label>
                        <input type="file" id="residence_certificate" name="residence_certificate" accept=".jpg,.jpeg,.png,.pdf" <?= !in_array('residence_certificate', $uploaded_docs) ? 'required' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label for="family_certificate">
                            가족관계증명서 <?= in_array('family_certificate', $uploaded_docs) ? '<span class="badge badge-success">✓ 업로드 완료</span>' : '<span style="color: red;">*</span>' ?>
                        </label>
                        <input type="file" id="family_certificate" name="family_certificate" accept=".jpg,.jpeg,.png,.pdf" <?= !in_array('family_certificate', $uploaded_docs) ? 'required' : '' ?>>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <a href="apply.php?event_id=<?= $event_id ?>&step=1" class="btn btn-secondary" style="flex: 1;">이전</a>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">서류 제출 및 다음</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
