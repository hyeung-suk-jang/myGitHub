<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
requireAdmin();

$pageTitle = '셀러 관리';

$db = Database::getInstance();

$sellerId = getQuery('id');

// 승인/거절 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sellerId) {
    $action = getPost('action');
    $rejectionReason = getPost('rejection_reason');

    if ($action === 'approve') {
        $db->beginTransaction();
        try {
            // 셀러 승인
            $db->execute(
                "UPDATE sellers SET seller_status = 'approved', approved_at = NOW() WHERE id = ?",
                [$sellerId]
            );

            // 사용자 타입 변경
            $seller = $db->fetchOne("SELECT user_id FROM sellers WHERE id = ?", [$sellerId]);
            $db->execute(
                "UPDATE users SET user_type = 'seller' WHERE id = ?",
                [$seller['user_id']]
            );

            $db->commit();
            Session::setFlash('success', '셀러가 승인되었습니다.');
        } catch (Exception $e) {
            $db->rollback();
            Session::setFlash('error', '승인 처리 중 오류가 발생했습니다.');
        }
    } elseif ($action === 'reject') {
        $db->execute(
            "UPDATE sellers SET seller_status = 'rejected', rejection_reason = ? WHERE id = ?",
            [$rejectionReason, $sellerId]
        );
        Session::setFlash('success', '셀러 신청이 거절되었습니다.');
    }

    redirect('/solution-marketplace/admin/sellers.php');
}

// 셀러 목록 조회
$status = getQuery('status', 'all');
$where = [];
$params = [];

if ($status !== 'all') {
    $where[] = "s.seller_status = ?";
    $params[] = $status;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sellers = $db->fetchAll(
    "SELECT s.*, u.name, u.email, u.phone
     FROM sellers s
     JOIN users u ON s.user_id = u.id
     $whereClause
     ORDER BY s.created_at DESC",
    $params
);

// 상세보기
$sellerDetail = null;
if ($sellerId) {
    $sellerDetail = $db->fetchOne(
        "SELECT s.*, u.name, u.email, u.phone
         FROM sellers s
         JOIN users u ON s.user_id = u.id
         WHERE s.id = ?",
        [$sellerId]
    );
}

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-sellers-container">
    <h2>셀러 관리</h2>

    <?php if ($sellerDetail): ?>
        <!-- 셀러 상세 정보 -->
        <div class="seller-detail-box">
            <h3>셀러 상세 정보</h3>

            <div class="detail-section">
                <h4>기본 정보</h4>
                <p><strong>이름:</strong> <?php echo escape($sellerDetail['name']); ?></p>
                <p><strong>이메일:</strong> <?php echo escape($sellerDetail['email']); ?></p>
                <p><strong>전화번호:</strong> <?php echo escape($sellerDetail['phone']); ?></p>
            </div>

            <div class="detail-section">
                <h4>사업자 정보</h4>
                <p><strong>사업자명:</strong> <?php echo escape($sellerDetail['business_name']); ?></p>
                <p><strong>사업자등록번호:</strong> <?php echo escape($sellerDetail['business_number'] ?: '미등록'); ?></p>
            </div>

            <div class="detail-section">
                <h4>정산 계좌</h4>
                <p><strong>은행:</strong> <?php echo escape($sellerDetail['bank_name']); ?></p>
                <p><strong>계좌번호:</strong> <?php echo escape($sellerDetail['account_number']); ?></p>
                <p><strong>예금주:</strong> <?php echo escape($sellerDetail['account_holder']); ?></p>
            </div>

            <div class="detail-section">
                <h4>상태 정보</h4>
                <p><strong>신청일:</strong> <?php echo formatDate($sellerDetail['created_at']); ?></p>
                <p><strong>상태:</strong>
                    <?php
                    $statusLabels = [
                        'pending' => '승인 대기',
                        'approved' => '승인됨',
                        'rejected' => '거절됨'
                    ];
                    echo $statusLabels[$sellerDetail['seller_status']] ?? $sellerDetail['seller_status'];
                    ?>
                </p>
                <?php if ($sellerDetail['seller_status'] === 'approved'): ?>
                    <p><strong>승인일:</strong> <?php echo formatDate($sellerDetail['approved_at']); ?></p>
                <?php endif; ?>
                <?php if ($sellerDetail['seller_status'] === 'rejected' && $sellerDetail['rejection_reason']): ?>
                    <p><strong>거절 사유:</strong> <?php echo escape($sellerDetail['rejection_reason']); ?></p>
                <?php endif; ?>
            </div>

            <?php if ($sellerDetail['seller_status'] === 'pending'): ?>
                <div class="seller-actions">
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('이 셀러를 승인하시겠습니까?');">
                            승인하기
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger" onclick="showRejectForm()">
                        거절하기
                    </button>

                    <div id="rejectForm" style="display:none; margin-top:20px;">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="reject">
                            <div class="form-group">
                                <label>거절 사유</label>
                                <textarea name="rejection_reason" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">거절 확정</button>
                            <button type="button" class="btn btn-secondary" onclick="hideRejectForm()">취소</button>
                        </form>
                    </div>
                </div>

                <script>
                function showRejectForm() {
                    document.getElementById('rejectForm').style.display = 'block';
                }
                function hideRejectForm() {
                    document.getElementById('rejectForm').style.display = 'none';
                }
                </script>
            <?php endif; ?>

            <div class="back-link">
                <a href="/solution-marketplace/admin/sellers.php" class="btn btn-secondary">목록으로</a>
            </div>
        </div>
    <?php else: ?>
        <!-- 셀러 목록 -->
        <div class="filter-section">
            <a href="?status=all" class="btn <?php echo $status === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                전체
            </a>
            <a href="?status=pending" class="btn <?php echo $status === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">
                승인 대기
            </a>
            <a href="?status=approved" class="btn <?php echo $status === 'approved' ? 'btn-primary' : 'btn-secondary'; ?>">
                승인됨
            </a>
            <a href="?status=rejected" class="btn <?php echo $status === 'rejected' ? 'btn-primary' : 'btn-secondary'; ?>">
                거절됨
            </a>
        </div>

        <?php if (empty($sellers)): ?>
            <p class="no-data">셀러가 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>이름</th>
                        <th>이메일</th>
                        <th>사업자명</th>
                        <th>상태</th>
                        <th>신청일</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sellers as $seller): ?>
                        <tr>
                            <td><?php echo escape($seller['name']); ?></td>
                            <td><?php echo escape($seller['email']); ?></td>
                            <td><?php echo escape($seller['business_name']); ?></td>
                            <td>
                                <?php if ($seller['seller_status'] === 'approved'): ?>
                                    <span class="badge badge-success">승인됨</span>
                                <?php elseif ($seller['seller_status'] === 'pending'): ?>
                                    <span class="badge badge-warning">대기중</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">거절됨</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($seller['created_at'], 'Y-m-d'); ?></td>
                            <td>
                                <a href="?id=<?php echo $seller['id']; ?>" class="btn btn-sm">상세보기</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
