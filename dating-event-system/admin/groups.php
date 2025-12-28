<?php
require_once '../config/database.php';
require_once '../config/functions.php';
checkAdminLogin();
$pdo = getDBConnection();

$event_id = $_GET['event_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_group') {
        $stmt = $pdo->prepare("INSERT INTO groups (event_id, group_name, group_number) VALUES (?, ?, ?)");
        $stmt->execute([$event_id, $_POST['group_name'], $_POST['group_number']]);
        $success = '그룹이 생성되었습니다.';
    }
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if ($event) {
    $stmt = $pdo->prepare("
        SELECT g.*, COUNT(gm.id) as member_count
        FROM groups g
        LEFT JOIN group_members gm ON g.id = gm.group_id
        WHERE g.event_id = ?
        GROUP BY g.id
        ORDER BY g.group_number
    ");
    $stmt->execute([$event_id]);
    $groups = $stmt->fetchAll();

    // 체크인한 참석자
    $stmt = $pdo->prepare("
        SELECT u.*, r.id as reg_id
        FROM users u
        JOIN registrations r ON u.id = r.user_id
        JOIN checkins c ON r.id = c.registration_id
        WHERE r.event_id = ?
        ORDER BY u.gender, u.name
    ");
    $stmt->execute([$event_id]);
    $participants = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>그룹 관리</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <header>
        <h1>💼 조별 그룹 관리</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">대시보드</a></li>
                    <li><a href="events.php">행사 관리</a></li>
                    <li><a href="applicants.php">신청자 관리</a></li>
                    <li><a href="groups.php" class="active">그룹 관리</a></li>
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

        <?php if ($event): ?>
            <div class="card">
                <h2><?= htmlspecialchars($event['title']) ?> - 그룹 관리</h2>

                <h3>새 그룹 생성</h3>
                <form method="POST" style="margin-bottom: 30px;">
                    <input type="hidden" name="action" value="create_group">
                    <div class="form-row">
                        <div class="form-group">
                            <label>그룹명</label>
                            <input type="text" name="group_name" required>
                        </div>
                        <div class="form-group">
                            <label>그룹 번호</label>
                            <input type="number" name="group_number" required>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary">그룹 생성</button>
                        </div>
                    </div>
                </form>

                <h3>등록된 그룹</h3>
                <?php if (empty($groups)): ?>
                    <p>생성된 그룹이 없습니다.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>그룹 번호</th>
                                <th>그룹명</th>
                                <th>멤버 수</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groups as $g): ?>
                            <tr>
                                <td><?= $g['group_number'] ?></td>
                                <td><?= htmlspecialchars($g['group_name']) ?></td>
                                <td><?= $g['member_count'] ?>명</td>
                                <td><a href="group-detail.php?group_id=<?= $g['id'] ?>" class="btn btn-sm btn-info">상세보기</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>참석자 목록</h2>
                <p>총 <?= count($participants) ?>명 (체크인 완료)</p>
                <table>
                    <thead>
                        <tr>
                            <th>이름</th>
                            <th>성별</th>
                            <th>나이</th>
                            <th>직업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= $p['gender'] === 'M' ? '남' : '여' ?></td>
                            <td><?= calculateAge($p['birth_date']) ?>세</td>
                            <td><?= htmlspecialchars($p['occupation'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">행사를 선택해주세요.</div>
            <a href="events.php" class="btn btn-primary">행사 목록으로</a>
        <?php endif; ?>
    </div>
</body>
</html>
