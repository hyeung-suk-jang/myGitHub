<?php
require_once '../config/database.php';
require_once '../config/functions.php';
checkAdminLogin();
$pdo = getDBConnection();

$event_id = $_GET['event_id'] ?? 0;

// 라운드 시작/종료 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'start_round') {
        $round = $_POST['round'];
        $stmt = $pdo->prepare("INSERT INTO selection_rounds (event_id, round, status, start_time) VALUES (?, ?, 'active', NOW()) ON DUPLICATE KEY UPDATE status = 'active', start_time = NOW()");
        $stmt->execute([$event_id, $round]);
        $success = "{$round}차 선택이 시작되었습니다.";
    } elseif ($_POST['action'] === 'end_round') {
        $round = $_POST['round'];
        $stmt = $pdo->prepare("UPDATE selection_rounds SET status = 'completed', end_time = NOW() WHERE event_id = ? AND round = ?");
        $stmt->execute([$event_id, $round]);

        // 매칭 처리
        $result = processMatching($pdo, $event_id, $round);
        $success = "{$round}차 선택이 종료되었습니다. 매칭: {$result['matched_count']}쌍";
    }
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if ($event) {
    // 라운드 상태
    $stmt = $pdo->prepare("SELECT * FROM selection_rounds WHERE event_id = ? ORDER BY round");
    $stmt->execute([$event_id]);
    $rounds = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 각 라운드별 선택 결과
    $all_selections = [];
    for ($r = 1; $r <= 3; $r++) {
        $stmt = $pdo->prepare("
            SELECT s.*,
                   u1.name as selector_name,
                   u2.name as selected_name
            FROM selections s
            JOIN users u1 ON s.selector_id = u1.id
            JOIN users u2 ON s.selected_id = u2.id
            WHERE s.event_id = ? AND s.round = ?
            ORDER BY s.selector_id, s.preference
        ");
        $stmt->execute([$event_id, $r]);
        $all_selections[$r] = $stmt->fetchAll();
    }

    // 매칭 결과
    $stmt = $pdo->prepare("
        SELECT m.*,
               u1.name as user1_name,
               u2.name as user2_name
        FROM matches m
        JOIN users u1 ON m.user1_id = u1.id
        JOIN users u2 ON m.user2_id = u2.id
        WHERE m.event_id = ?
        ORDER BY m.round, m.match_time
    ");
    $stmt->execute([$event_id]);
    $matches = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>선택 결과</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <header>
        <h1>💼 선택 결과 관리</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">대시보드</a></li>
                    <li><a href="events.php">행사 관리</a></li>
                    <li><a href="applicants.php">신청자 관리</a></li>
                    <li><a href="groups.php">그룹 관리</a></li>
                    <li><a href="selections.php" class="active">선택 결과</a></li>
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
                <h2><?= htmlspecialchars($event['title']) ?> - 선택 라운드 관리</h2>

                <div class="form-row">
                    <?php for ($r = 1; $r <= 3; $r++): ?>
                    <div class="form-group">
                        <h3><?= $r ?>차 선택</h3>
                        <?php if (!isset($rounds[$r]) || $rounds[$r] === 'waiting'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="start_round">
                                <input type="hidden" name="round" value="<?= $r ?>">
                                <button type="submit" class="btn btn-success">시작</button>
                            </form>
                        <?php elseif ($rounds[$r] === 'active'): ?>
                            <span class="badge badge-success">진행중</span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="end_round">
                                <input type="hidden" name="round" value="<?= $r ?>">
                                <button type="submit" class="btn btn-danger">종료 및 매칭</button>
                            </form>
                        <?php else: ?>
                            <span class="badge badge-secondary">완료</span>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <?php foreach ([1, 2, 3] as $r): ?>
                <?php if (!empty($all_selections[$r])): ?>
                <div class="card">
                    <h2><?= $r ?>차 선택 결과 (<?= count($all_selections[$r]) ?>건)</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>선택자</th>
                                <th>지망</th>
                                <th>선택된 사람</th>
                                <th>시간</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_selections[$r] as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['selector_name']) ?> (ID: <?= $s['selector_id'] ?>)</td>
                                <td><?= $s['preference'] ?>지망</td>
                                <td><?= htmlspecialchars($s['selected_name']) ?> (ID: <?= $s['selected_id'] ?>)</td>
                                <td><?= $s['selection_time'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="card">
                <h2>매칭 결과 (총 <?= count($matches) ?>쌍)</h2>
                <?php if (empty($matches)): ?>
                    <p>아직 매칭된 커플이 없습니다.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>라운드</th>
                                <th>커플</th>
                                <th>매칭 시간</th>
                                <th>상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matches as $m): ?>
                            <tr>
                                <td><?= $m['round'] ?>차</td>
                                <td>
                                    <?= htmlspecialchars($m['user1_name']) ?> (ID: <?= $m['user1_id'] ?>)
                                    ❤️
                                    <?= htmlspecialchars($m['user2_name']) ?> (ID: <?= $m['user2_id'] ?>)
                                </td>
                                <td><?= $m['match_time'] ?></td>
                                <td><span class="badge badge-success"><?= $m['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">행사를 선택해주세요.</div>
            <a href="events.php" class="btn btn-primary">행사 목록으로</a>
        <?php endif; ?>
    </div>
</body>
</html>
