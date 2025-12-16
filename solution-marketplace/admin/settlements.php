<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
requireAdmin();

$pageTitle = '정산 관리';

$db = Database::getInstance();

// 정산 생성 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('action') === 'create_settlements') {
    $settlementMonth = getPost('settlement_month');

    if (!$settlementMonth) {
        Session::setFlash('error', '정산 월을 선택해주세요.');
    } else {
        // 해당 월의 셀러별 매출 집계
        $startDate = $settlementMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $salesData = $db->fetchAll(
            "SELECT oi.seller_id, SUM(oi.price) as total_sales
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             WHERE o.order_status = 'completed'
             AND DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY oi.seller_id",
            [$startDate, $endDate]
        );

        $db->beginTransaction();
        try {
            foreach ($salesData as $data) {
                $sellerId = $data['seller_id'];
                $totalSales = $data['total_sales'];
                $commissionAmount = $totalSales * (COMMISSION_RATE / 100);
                $settlementAmount = $totalSales - $commissionAmount;

                // 이미 정산 데이터가 있는지 확인
                $existing = $db->fetchOne(
                    "SELECT id FROM settlements WHERE seller_id = ? AND settlement_month = ?",
                    [$sellerId, $settlementMonth]
                );

                if ($existing) {
                    // 업데이트
                    $db->execute(
                        "UPDATE settlements
                         SET total_sales = ?, commission_amount = ?, settlement_amount = ?
                         WHERE id = ?",
                        [$totalSales, $commissionAmount, $settlementAmount, $existing['id']]
                    );
                } else {
                    // 새로 생성
                    $db->insert(
                        "INSERT INTO settlements
                         (seller_id, settlement_month, total_sales, commission_rate, commission_amount, settlement_amount, settlement_status)
                         VALUES (?, ?, ?, ?, ?, ?, 'pending')",
                        [$sellerId, $settlementMonth, $totalSales, COMMISSION_RATE, $commissionAmount, $settlementAmount]
                    );
                }
            }

            $db->commit();
            Session::setFlash('success', $settlementMonth . ' 정산 데이터가 생성/업데이트되었습니다.');
        } catch (Exception $e) {
            $db->rollback();
            Session::setFlash('error', '정산 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }

        redirect('/solution-marketplace/admin/settlements.php');
    }
}

// 정산 완료 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('action') === 'complete_settlement') {
    $settlementId = getPost('settlement_id');

    $result = $db->execute(
        "UPDATE settlements SET settlement_status = 'completed', settled_at = NOW() WHERE id = ?",
        [$settlementId]
    );

    if ($result) {
        Session::setFlash('success', '정산이 완료되었습니다.');
    } else {
        Session::setFlash('error', '정산 처리에 실패했습니다.');
    }

    redirect('/solution-marketplace/admin/settlements.php');
}

// 정산 목록 조회
$month = getQuery('month');
$status = getQuery('status', 'all');

$where = [];
$params = [];

if ($month) {
    $where[] = "s.settlement_month = ?";
    $params[] = $month;
}

if ($status !== 'all') {
    $where[] = "s.settlement_status = ?";
    $params[] = $status;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$settlements = $db->fetchAll(
    "SELECT s.*, sel.business_name, u.name as seller_name, sel.bank_name, sel.account_number, sel.account_holder
     FROM settlements s
     JOIN sellers sel ON s.seller_id = sel.id
     JOIN users u ON sel.user_id = u.id
     $whereClause
     ORDER BY s.settlement_month DESC, s.id DESC",
    $params
);

// 정산 월 목록
$months = $db->fetchAll(
    "SELECT DISTINCT settlement_month FROM settlements ORDER BY settlement_month DESC LIMIT 12"
);

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-settlements-container">
    <h2>정산 관리</h2>

    <div class="settlement-create-section">
        <h3>정산 데이터 생성</h3>
        <form method="POST" action="" class="create-settlement-form">
            <input type="hidden" name="action" value="create_settlements">
            <div class="form-group">
                <label for="settlement_month">정산 월 선택</label>
                <input type="month" id="settlement_month" name="settlement_month"
                       value="<?php echo date('Y-m', strtotime('-1 month')); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">정산 데이터 생성/업데이트</button>
        </form>
    </div>

    <div class="filter-section">
        <div class="filter-group">
            <label>정산 월:</label>
            <select onchange="location.href='?month=' + this.value + '&status=<?php echo $status; ?>'">
                <option value="">전체</option>
                <?php foreach ($months as $m): ?>
                    <option value="<?php echo $m['settlement_month']; ?>"
                            <?php echo $month === $m['settlement_month'] ? 'selected' : ''; ?>>
                        <?php echo $m['settlement_month']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>상태:</label>
            <a href="?month=<?php echo $month; ?>&status=all"
               class="btn btn-sm <?php echo $status === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">전체</a>
            <a href="?month=<?php echo $month; ?>&status=pending"
               class="btn btn-sm <?php echo $status === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">대기</a>
            <a href="?month=<?php echo $month; ?>&status=completed"
               class="btn btn-sm <?php echo $status === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">완료</a>
        </div>
    </div>

    <?php if (empty($settlements)): ?>
        <p class="no-data">정산 내역이 없습니다.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>정산 월</th>
                    <th>셀러</th>
                    <th>사업자명</th>
                    <th>총 매출</th>
                    <th>수수료 (<?php echo COMMISSION_RATE; ?>%)</th>
                    <th>정산 금액</th>
                    <th>계좌 정보</th>
                    <th>상태</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($settlements as $settlement): ?>
                    <tr>
                        <td><?php echo $settlement['settlement_month']; ?></td>
                        <td><?php echo escape($settlement['seller_name']); ?></td>
                        <td><?php echo escape($settlement['business_name']); ?></td>
                        <td><?php echo formatPrice($settlement['total_sales']); ?></td>
                        <td><?php echo formatPrice($settlement['commission_amount']); ?></td>
                        <td class="highlight"><?php echo formatPrice($settlement['settlement_amount']); ?></td>
                        <td>
                            <?php echo escape($settlement['bank_name']); ?><br>
                            <?php echo escape($settlement['account_number']); ?><br>
                            <?php echo escape($settlement['account_holder']); ?>
                        </td>
                        <td>
                            <?php if ($settlement['settlement_status'] === 'completed'): ?>
                                <span class="badge badge-success">완료</span><br>
                                <small><?php echo formatDate($settlement['settled_at'], 'Y-m-d'); ?></small>
                            <?php elseif ($settlement['settlement_status'] === 'processing'): ?>
                                <span class="badge badge-warning">처리중</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">대기</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($settlement['settlement_status'] === 'pending'): ?>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="complete_settlement">
                                    <input type="hidden" name="settlement_id" value="<?php echo $settlement['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('정산을 완료하시겠습니까?');">
                                        정산 완료
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>합계</strong></td>
                    <td><strong><?php echo formatPrice(array_sum(array_column($settlements, 'total_sales'))); ?></strong></td>
                    <td><strong><?php echo formatPrice(array_sum(array_column($settlements, 'commission_amount'))); ?></strong></td>
                    <td><strong><?php echo formatPrice(array_sum(array_column($settlements, 'settlement_amount'))); ?></strong></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
