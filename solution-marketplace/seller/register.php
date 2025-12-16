<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();

$pageTitle = '셀러 등록';

// 로그인 필요
if (!Session::isLoggedIn()) {
    Session::setFlash('error', '셀러 등록을 위해 먼저 로그인해주세요.');
    redirect('/solution-marketplace/public/login.php');
}

// 이미 셀러인지 확인
$sellerInfo = getSellerInfo(Session::getUserId());
if ($sellerInfo) {
    if ($sellerInfo['seller_status'] === 'approved') {
        Session::setFlash('info', '이미 승인된 셀러입니다.');
        redirect('/solution-marketplace/seller/index.php');
    } elseif ($sellerInfo['seller_status'] === 'pending') {
        Session::setFlash('info', '셀러 승인 대기 중입니다.');
        redirect('/solution-marketplace/public/index.php');
    }
}

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $businessName = getPost('business_name');
    $businessNumber = getPost('business_number');
    $bankName = getPost('bank_name');
    $accountNumber = getPost('account_number');
    $accountHolder = getPost('account_holder');

    $errors = [];

    if (empty($businessName)) {
        $errors[] = '사업자명을 입력해주세요.';
    }

    if (empty($bankName)) {
        $errors[] = '은행명을 입력해주세요.';
    }

    if (empty($accountNumber)) {
        $errors[] = '계좌번호를 입력해주세요.';
    }

    if (empty($accountHolder)) {
        $errors[] = '예금주명을 입력해주세요.';
    }

    if (empty($errors)) {
        $result = registerSeller(Session::getUserId(), [
            'business_name' => $businessName,
            'business_number' => $businessNumber,
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_holder' => $accountHolder
        ]);

        if ($result['success']) {
            Session::setFlash('success', '셀러 등록 신청이 완료되었습니다. 관리자 승인 후 이용 가능합니다.');
            redirect('/solution-marketplace/public/index.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="seller-register-container">
    <div class="seller-register-box">
        <h2>셀러 등록 신청</h2>
        <p class="description">셀러로 등록하여 상품을 판매하세요. 신청 후 관리자 승인이 필요합니다.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="seller-register-form">
            <div class="form-group">
                <label for="business_name">사업자명 (개인/상호명) *</label>
                <input type="text" id="business_name" name="business_name" required
                       value="<?php echo isset($businessName) ? escape($businessName) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="business_number">사업자등록번호</label>
                <input type="text" id="business_number" name="business_number"
                       placeholder="123-45-67890"
                       value="<?php echo isset($businessNumber) ? escape($businessNumber) : ''; ?>">
                <small>개인 셀러는 입력하지 않아도 됩니다.</small>
            </div>

            <h3>정산 계좌 정보</h3>

            <div class="form-group">
                <label for="bank_name">은행명 *</label>
                <input type="text" id="bank_name" name="bank_name" required
                       placeholder="예: 국민은행"
                       value="<?php echo isset($bankName) ? escape($bankName) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="account_number">계좌번호 *</label>
                <input type="text" id="account_number" name="account_number" required
                       placeholder="-없이 숫자만 입력"
                       value="<?php echo isset($accountNumber) ? escape($accountNumber) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="account_holder">예금주명 *</label>
                <input type="text" id="account_holder" name="account_holder" required
                       value="<?php echo isset($accountHolder) ? escape($accountHolder) : ''; ?>">
            </div>

            <div class="form-notice">
                <p>* 정산은 매월 말일에 진행되며, 수수료 <?php echo COMMISSION_RATE; ?>%가 차감됩니다.</p>
                <p>* 승인 후 셀러 대시보드에서 상품을 등록할 수 있습니다.</p>
            </div>

            <button type="submit" class="btn btn-primary btn-block">셀러 등록 신청</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
