<?php
require_once '../config/database.php';
require_once '../config/functions.php';
checkAdminLogin();
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, location_detail, max_participants, male_capacity, female_capacity, registration_fee, registration_start_date, registration_end_date, schedule_detail, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['title'], $_POST['description'], $_POST['event_date'], $_POST['event_time'], $_POST['location'], $_POST['location_detail'], $_POST['max_participants'], $_POST['male_capacity'], $_POST['female_capacity'], $_POST['registration_fee'], $_POST['registration_start_date'], $_POST['registration_end_date'], $_POST['schedule_detail'], $_POST['status']]);
        $success = '행사가 등록되었습니다.';
    }
}

$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>행사 관리</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <header>
        <h1>💼 행사 관리</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">대시보드</a></li>
                    <li><a href="events.php" class="active">행사 관리</a></li>
                    <li><a href="applicants.php">신청자 관리</a></li>
                    <li><a href="groups.php">그룹 관리</a></li>
                    <li><a href="selections.php">선택 결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>새 행사 등록</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group">
                        <label>행사명 *</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>행사 날짜 *</label>
                        <input type="date" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label>행사 시간 *</label>
                        <input type="time" name="event_time" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>장소 *</label>
                        <input type="text" name="location" required>
                    </div>
                    <div class="form-group">
                        <label>참가비 *</label>
                        <input type="number" name="registration_fee" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>최대 인원 *</label>
                        <input type="number" name="max_participants" required>
                    </div>
                    <div class="form-group">
                        <label>남성 정원 *</label>
                        <input type="number" name="male_capacity" required>
                    </div>
                    <div class="form-group">
                        <label>여성 정원 *</label>
                        <input type="number" name="female_capacity" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>접수 시작일 *</label>
                        <input type="datetime-local" name="registration_start_date" required>
                    </div>
                    <div class="form-group">
                        <label>접수 마감일 *</label>
                        <input type="datetime-local" name="registration_end_date" required>
                    </div>
                    <div class="form-group">
                        <label>상태 *</label>
                        <select name="status" required>
                            <option value="upcoming">예정</option>
                            <option value="registration_open">접수중</option>
                            <option value="registration_closed">접수마감</option>
                            <option value="ongoing">진행중</option>
                            <option value="completed">완료</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>행사 소개</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>상세 주소</label>
                    <textarea name="location_detail" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>스케줄 상세</label>
                    <textarea name="schedule_detail" rows="5"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">행사 등록</button>
            </form>
        </div>

        <div class="card">
            <h2>등록된 행사 목록</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>행사명</th>
                        <th>일시</th>
                        <th>장소</th>
                        <th>정원</th>
                        <th>상태</th>
                        <th>참가비</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $e): ?>
                    <tr>
                        <td><?= $e['id'] ?></td>
                        <td><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= $e['event_date'] ?> <?= substr($e['event_time'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($e['location']) ?></td>
                        <td>남 <?= $e['male_capacity'] ?> / 여 <?= $e['female_capacity'] ?></td>
                        <td><span class="badge badge-info"><?= $e['status'] ?></span></td>
                        <td><?= number_format($e['registration_fee']) ?>원</td>
                        <td class="actions">
                            <a href="applicants.php?event_id=<?= $e['id'] ?>" class="btn btn-sm btn-info">신청자</a>
                            <a href="selections.php?event_id=<?= $e['id'] ?>" class="btn btn-sm btn-success">선택결과</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
