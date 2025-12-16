<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();
requireLogin();

$pageTitle = '상품 등록';

$db = Database::getInstance();
$userId = Session::getUserId();

// 셀러 정보 확인
$sellerInfo = getSellerInfo($userId);

if (!$sellerInfo || !isApprovedSeller($userId)) {
    Session::setFlash('error', '승인된 셀러만 상품을 등록할 수 있습니다.');
    redirect('/solution-marketplace/seller/index.php');
}

$sellerId = $sellerInfo['id'];

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = getPost('title');
    $description = getPost('description');
    $price = getPost('price');
    $productType = getPost('product_type');

    $errors = [];

    if (empty($title)) {
        $errors[] = '상품명을 입력해주세요.';
    }

    if (empty($description)) {
        $errors[] = '상품 설명을 입력해주세요.';
    }

    if (empty($price) || $price <= 0) {
        $errors[] = '올바른 가격을 입력해주세요.';
    }

    if (!in_array($productType, ['ebook', 'source', 'video'])) {
        $errors[] = '올바른 상품 타입을 선택해주세요.';
    }

    // 파일 업로드 처리
    $filePath = null;
    $thumbnailPath = null;

    if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = [];
        $uploadSubDir = '';

        switch ($productType) {
            case 'ebook':
                $allowedTypes = ['pdf', 'epub'];
                $uploadSubDir = 'ebooks/';
                break;
            case 'source':
                $allowedTypes = ['zip', 'rar'];
                $uploadSubDir = 'sources/';
                break;
            case 'video':
                $allowedTypes = ['mp4', 'avi', 'mov'];
                $uploadSubDir = 'videos/';
                break;
        }

        $uploadDir = UPLOAD_DIR . $uploadSubDir;
        $fileResult = uploadFile($_FILES['product_file'], $uploadDir, $allowedTypes);

        if ($fileResult['success']) {
            $filePath = '/solution-marketplace/public/uploads/' . $uploadSubDir . $fileResult['filename'];
        } else {
            $errors[] = '파일 업로드 실패: ' . $fileResult['message'];
        }
    } else {
        $errors[] = '상품 파일을 업로드해주세요.';
    }

    // 썸네일 업로드 처리
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = UPLOAD_DIR . 'thumbnails/';
        $thumbnailResult = uploadFile($_FILES['thumbnail'], $uploadDir, ['jpg', 'jpeg', 'png', 'gif']);

        if ($thumbnailResult['success']) {
            $thumbnailPath = '/solution-marketplace/public/uploads/thumbnails/' . $thumbnailResult['filename'];
        }
    }

    if (empty($errors)) {
        $productId = $db->insert(
            "INSERT INTO products (seller_id, product_type, title, description, price, file_path, thumbnail_path, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'active')",
            [$sellerId, $productType, $title, $description, $price, $filePath, $thumbnailPath]
        );

        if ($productId) {
            Session::setFlash('success', '상품이 등록되었습니다.');
            redirect('/solution-marketplace/seller/products/list.php');
        } else {
            $errors[] = '상품 등록에 실패했습니다.';
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="product-add-container">
    <h2>상품 등록</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="product-form">
        <div class="form-group">
            <label for="product_type">상품 타입 *</label>
            <select id="product_type" name="product_type" required>
                <option value="">선택하세요</option>
                <option value="ebook" <?php echo (isset($productType) && $productType === 'ebook') ? 'selected' : ''; ?>>
                    전자책 (PDF, EPUB)
                </option>
                <option value="source" <?php echo (isset($productType) && $productType === 'source') ? 'selected' : ''; ?>>
                    솔루션 소스 (ZIP, RAR)
                </option>
                <option value="video" <?php echo (isset($productType) && $productType === 'video') ? 'selected' : ''; ?>>
                    동영상 강의 (MP4, AVI, MOV)
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="title">상품명 *</label>
            <input type="text" id="title" name="title" required
                   value="<?php echo isset($title) ? escape($title) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="description">상품 설명 *</label>
            <textarea id="description" name="description" rows="10" required><?php echo isset($description) ? escape($description) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">가격 (원) *</label>
            <input type="number" id="price" name="price" min="0" step="100" required
                   value="<?php echo isset($price) ? escape($price) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="product_file">상품 파일 *</label>
            <input type="file" id="product_file" name="product_file" required>
            <small>전자책: PDF, EPUB / 소스: ZIP, RAR / 동영상: MP4, AVI, MOV (최대 100MB)</small>
        </div>

        <div class="form-group">
            <label for="thumbnail">썸네일 이미지</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
            <small>JPG, PNG, GIF 형식 (선택사항)</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">등록하기</button>
            <a href="/solution-marketplace/seller/products/list.php" class="btn btn-secondary">취소</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
