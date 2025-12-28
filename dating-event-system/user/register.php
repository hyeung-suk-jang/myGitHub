<?php
require_once '../config/database.php';
require_once '../config/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();

    try {
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $name = $_POST['name'];
        $gender = $_POST['gender'];
        $birth_date = $_POST['birth_date'];
        $email = $_POST['email'];
        $occupation = $_POST['occupation'] ?? '';
        $company = $_POST['company'] ?? '';
        $education = $_POST['education'] ?? '';
        $height = $_POST['height'] ?? null;
        $religion = $_POST['religion'] ?? '';
        $smoking = $_POST['smoking'] ?? 'N';
        $drinking = $_POST['drinking'] ?? '';
        $hobby = $_POST['hobby'] ?? '';
        $introduction = $_POST['introduction'] ?? '';

        // 기존 고객 여부 확인
        $is_existing = isset($_POST['is_existing_customer']) && $_POST['is_existing_customer'] === '1';
        $customer_id = $_POST['customer_id'] ?? '';

        // 중복 확인
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = '이미 등록된 전화번호입니다.';
        } else {
            // 회원 등록
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    phone, password, name, gender, birth_date, email,
                    occupation, company, education, height, religion,
                    smoking, drinking, hobby, introduction,
                    is_existing_customer, customer_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $phone, $password, $name, $gender, $birth_date, $email,
                $occupation, $company, $education, $height, $religion,
                $smoking, $drinking, $hobby, $introduction,
                $is_existing, $customer_id
            ]);

            $success = '회원가입이 완료되었습니다. 로그인해주세요.';

            // 자동 로그인
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;

            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $error = '회원가입 중 오류가 발생했습니다: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💑 회원가입</h1>
    </header>

    <div class="container">
        <div class="card">
            <h2>회원가입 및 프로필 등록</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= escape($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= escape($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <h3>기본 정보</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">이름 *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">전화번호 *</label>
                        <input type="tel" id="phone" name="phone" placeholder="01012345678" required>
                    </div>

                    <div class="form-group">
                        <label for="password">비밀번호 *</label>
                        <input type="password" id="password" name="password" minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">성별 *</label>
                        <select id="gender" name="gender" required>
                            <option value="">선택하세요</option>
                            <option value="M">남성</option>
                            <option value="F">여성</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="birth_date">생년월일 *</label>
                        <input type="date" id="birth_date" name="birth_date" required>
                    </div>

                    <div class="form-group">
                        <label for="email">이메일</label>
                        <input type="email" id="email" name="email">
                    </div>
                </div>

                <h3>기존 고객 정보</h3>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_existing_customer" value="1" id="existing_customer_check">
                        결혼정보 회사 상담 이력이 있습니다
                    </label>
                </div>

                <div class="form-group" id="customer_id_group" style="display: none;">
                    <label for="customer_id">고객 ID</label>
                    <input type="text" id="customer_id" name="customer_id" placeholder="상담 시 발급받은 고객 ID">
                </div>

                <h3>상세 프로필</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="occupation">직업</label>
                        <input type="text" id="occupation" name="occupation">
                    </div>

                    <div class="form-group">
                        <label for="company">회사명</label>
                        <input type="text" id="company" name="company">
                    </div>

                    <div class="form-group">
                        <label for="education">최종학력</label>
                        <select id="education" name="education">
                            <option value="">선택하세요</option>
                            <option value="고졸">고졸</option>
                            <option value="대졸(2-3년)">대졸(2-3년)</option>
                            <option value="대졸(4년)">대졸(4년)</option>
                            <option value="대학원졸">대학원졸</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="height">키 (cm)</label>
                        <input type="number" id="height" name="height" min="100" max="250">
                    </div>

                    <div class="form-group">
                        <label for="religion">종교</label>
                        <select id="religion" name="religion">
                            <option value="">선택하세요</option>
                            <option value="무교">무교</option>
                            <option value="기독교">기독교</option>
                            <option value="천주교">천주교</option>
                            <option value="불교">불교</option>
                            <option value="기타">기타</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="smoking">흡연</label>
                        <select id="smoking" name="smoking">
                            <option value="N">비흡연</option>
                            <option value="Y">흡연</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="drinking">음주</label>
                        <select id="drinking" name="drinking">
                            <option value="">선택하세요</option>
                            <option value="안함">안함</option>
                            <option value="가끔">가끔</option>
                            <option value="자주">자주</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="hobby">취미/특기</label>
                    <textarea id="hobby" name="hobby" placeholder="취미나 특기를 입력해주세요"></textarea>
                </div>

                <div class="form-group">
                    <label for="introduction">자기소개</label>
                    <textarea id="introduction" name="introduction" placeholder="자기소개를 입력해주세요"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">회원가입</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <p>이미 계정이 있으신가요? <a href="login.php">로그인</a></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#existing_customer_check').change(function() {
                if ($(this).is(':checked')) {
                    $('#customer_id_group').show();
                } else {
                    $('#customer_id_group').hide();
                    $('#customer_id').val('');
                }
            });
        });
    </script>
</body>
</html>
