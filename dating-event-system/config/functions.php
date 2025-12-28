<?php
// 공통 함수

// 사용자 로그인 확인
function checkUserLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /user/login.php');
        exit;
    }
}

// 관리자 로그인 확인
function checkAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

// 파일 업로드 함수
function uploadFile($file, $userId, $eventId, $documentType) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 중 오류가 발생했습니다.'];
    }

    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'message' => '파일 크기는 5MB를 초과할 수 없습니다.'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        return ['success' => false, 'message' => '허용되지 않는 파일 형식입니다.'];
    }

    $uploadDir = __DIR__ . '/../uploads/documents/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = $userId . '_' . $eventId . '_' . $documentType . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => true,
            'file_name' => $file['name'],
            'file_path' => '/uploads/documents/' . $fileName,
            'file_size' => $file['size']
        ];
    }

    return ['success' => false, 'message' => '파일 저장에 실패했습니다.'];
}

// 전화번호 포맷팅
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 10) {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $phone);
    } elseif (strlen($phone) == 11) {
        return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $phone);
    }
    return $phone;
}

// 나이 계산
function calculateAge($birthDate) {
    $birth = new DateTime($birthDate);
    $now = new DateTime();
    $age = $now->diff($birth);
    return $age->y;
}

// XSS 방지
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 날짜 포맷팅
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

// 금액 포맷팅
function formatMoney($amount) {
    return number_format($amount) . '원';
}

// 성별 표시
function getGenderText($gender) {
    return $gender === 'M' ? '남성' : '여성';
}

// 결제 상태 표시
function getPaymentStatusText($status) {
    $statusText = [
        'unpaid' => '미결제',
        'paid' => '결제완료',
        'refunded' => '환불완료'
    ];
    return $statusText[$status] ?? $status;
}

// 신청 상태 표시
function getRegistrationStatusText($status) {
    $statusText = [
        'pending' => '대기중',
        'approved' => '승인됨',
        'rejected' => '거부됨',
        'cancelled' => '취소됨'
    ];
    return $statusText[$status] ?? $status;
}

// 서류 타입 표시
function getDocumentTypeText($type) {
    $typeText = [
        'id_card' => '신분증',
        'salary_statement' => '3개월 급여통장 내역',
        'residence_certificate' => '주민등록등본',
        'family_certificate' => '가족관계증명서'
    ];
    return $typeText[$type] ?? $type;
}

// 매칭 알고리즘 - 서로 선택한 경우 매칭
function processMatching($pdo, $eventId, $round) {
    try {
        $pdo->beginTransaction();

        // 해당 라운드의 모든 선택을 가져옴
        $stmt = $pdo->prepare("
            SELECT selector_id, selected_id, preference
            FROM selections
            WHERE event_id = ? AND round = ? AND is_active = TRUE
            ORDER BY preference ASC
        ");
        $stmt->execute([$eventId, $round]);
        $selections = $stmt->fetchAll();

        $matches = [];
        $matched_users = [];

        // 1지망부터 차례로 확인
        for ($pref = 1; $pref <= 3; $pref++) {
            foreach ($selections as $selection) {
                if ($selection['preference'] != $pref) continue;
                if (in_array($selection['selector_id'], $matched_users)) continue;
                if (in_array($selection['selected_id'], $matched_users)) continue;

                // 상대방도 나를 선택했는지 확인
                $stmt = $pdo->prepare("
                    SELECT * FROM selections
                    WHERE event_id = ? AND round = ?
                    AND selector_id = ? AND selected_id = ?
                    AND is_active = TRUE
                ");
                $stmt->execute([
                    $eventId,
                    $round,
                    $selection['selected_id'],
                    $selection['selector_id']
                ]);
                $mutual = $stmt->fetch();

                if ($mutual) {
                    // 매칭 성공
                    $stmt = $pdo->prepare("
                        INSERT INTO matches (event_id, round, user1_id, user2_id)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $eventId,
                        $round,
                        $selection['selector_id'],
                        $selection['selected_id']
                    ]);

                    $matched_users[] = $selection['selector_id'];
                    $matched_users[] = $selection['selected_id'];
                }
            }
        }

        $pdo->commit();
        return ['success' => true, 'matched_count' => count($matched_users) / 2];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>
