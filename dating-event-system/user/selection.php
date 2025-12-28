<?php
require_once '../config/database.php';
require_once '../config/functions.php';

checkUserLogin();

$event_id = $_GET['event_id'] ?? 0;
$pdo = getDBConnection();

// 행사 정보
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}

// 사용자 정보
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

// 현재 활성화된 라운드 확인
$stmt = $pdo->prepare("
    SELECT * FROM selection_rounds
    WHERE event_id = ? AND status = 'active'
    ORDER BY round LIMIT 1
");
$stmt->execute([$event_id]);
$active_round = $stmt->fetch();

// 체크인한 이성 참석자 목록
$stmt = $pdo->prepare("
    SELECT u.*
    FROM users u
    JOIN registrations r ON u.id = r.user_id
    JOIN checkins c ON r.id = c.registration_id
    WHERE r.event_id = ? AND u.gender != ? AND u.id != ?
    ORDER BY u.name
");
$stmt->execute([$event_id, $current_user['gender'], $_SESSION['user_id']]);
$participants = $stmt->fetchAll();

// 이미 선택했는지 확인
$my_selections = [];
if ($active_round) {
    $stmt = $pdo->prepare("
        SELECT preference, selected_id
        FROM selections
        WHERE event_id = ? AND round = ? AND selector_id = ?
    ");
    $stmt->execute([$event_id, $active_round['round'], $_SESSION['user_id']]);
    $my_selections = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $active_round) {
    try {
        $pdo->beginTransaction();

        // 기존 선택 삭제
        $stmt = $pdo->prepare("
            DELETE FROM selections
            WHERE event_id = ? AND round = ? AND selector_id = ?
        ");
        $stmt->execute([$event_id, $active_round['round'], $_SESSION['user_id']]);

        // 새 선택 저장
        $selections = [
            1 => $_POST['first_choice'] ?? null,
            2 => $_POST['second_choice'] ?? null,
            3 => $_POST['third_choice'] ?? null
        ];

        foreach ($selections as $pref => $selected_id) {
            if ($selected_id && $selected_id != '') {
                $stmt = $pdo->prepare("
                    INSERT INTO selections (event_id, round, selector_id, selected_id, preference)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$event_id, $active_round['round'], $_SESSION['user_id'], $selected_id, $pref]);
            }
        }

        $pdo->commit();
        header('Location: selection.php?event_id=' . $event_id . '&success=1');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '선택 저장 중 오류가 발생했습니다: ' . $e->getMessage();
    }
}

$success = isset($_GET['success']) ? $_GET['success'] : false;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>선택하기 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💝 선택하기</h1>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="index.php">행사 신청</a></li>
                    <li><a href="event-info.php">행사 안내</a></li>
                    <li><a href="schedule.php">스케줄</a></li>
                    <li><a href="checkin.php">체크인</a></li>
                    <li><a href="participants.php">참석자</a></li>
                    <li><a href="selection.php" class="active">선택하기</a></li>
                    <li><a href="matching.php">매칭결과</a></li>
                    <li><a href="logout.php">로그아웃</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2><?= escape($event['title']) ?> - 선택하기</h2>

            <?php if (!$active_round): ?>
                <div class="alert alert-info">
                    <h3>⏰ 선택 시간이 아닙니다</h3>
                    <p>관리자가 선택을 시작하면 이 페이지에서 선택하실 수 있습니다.<br>
                    스케줄을 확인하고 선택 시간을 기다려주세요.</p>
                </div>
                <a href="schedule.php?event_id=<?= $event_id ?>" class="btn btn-primary">스케줄 보기</a>
                <a href="participants.php?event_id=<?= $event_id ?>" class="btn btn-secondary">참석자 보기</a>

            <?php else: ?>
                <div class="alert alert-success">
                    <strong>🎯 <?= $active_round['round'] ?>차 선택이 진행 중입니다!</strong><br>
                    마음에 드는 분을 1, 2, 3지망으로 선택해주세요. 서로 선택하면 매칭됩니다.
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        선택이 성공적으로 저장되었습니다!
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-error"><?= escape($message) ?></div>
                <?php endif; ?>

                <?php if (empty($participants)): ?>
                    <div class="alert alert-info">
                        선택할 수 있는 참석자가 없습니다.
                    </div>
                <?php else: ?>
                    <form method="POST" id="selectionForm">
                        <div class="form-group">
                            <label for="first_choice">💖 1지망</label>
                            <select id="first_choice" name="first_choice" class="selection-dropdown">
                                <option value="">선택하세요</option>
                                <?php foreach ($participants as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= (isset($my_selections[1]) && $my_selections[1] == $p['id']) ? 'selected' : '' ?>>
                                        [ID: <?= $p['id'] ?>] <?= escape($p['name']) ?> (<?= calculateAge($p['birth_date']) ?>세,
                                        <?= escape($p['occupation'] ?? '직업미상') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="second_choice">💗 2지망</label>
                            <select id="second_choice" name="second_choice" class="selection-dropdown">
                                <option value="">선택하세요</option>
                                <?php foreach ($participants as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= (isset($my_selections[2]) && $my_selections[2] == $p['id']) ? 'selected' : '' ?>>
                                        [ID: <?= $p['id'] ?>] <?= escape($p['name']) ?> (<?= calculateAge($p['birth_date']) ?>세,
                                        <?= escape($p['occupation'] ?? '직업미상') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="third_choice">💕 3지망</label>
                            <select id="third_choice" name="third_choice" class="selection-dropdown">
                                <option value="">선택하세요</option>
                                <?php foreach ($participants as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= (isset($my_selections[3]) && $my_selections[3] == $p['id']) ? 'selected' : '' ?>>
                                        [ID: <?= $p['id'] ?>] <?= escape($p['name']) ?> (<?= calculateAge($p['birth_date']) ?>세,
                                        <?= escape($p['occupation'] ?? '직업미상') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <strong>안내:</strong><br>
                            - 같은 사람을 중복으로 선택할 수 없습니다.<br>
                            - 선택을 변경하고 싶으면 다시 선택 후 저장하세요.<br>
                            - 상대방도 나를 선택해야 매칭이 성사됩니다.<br>
                            - 선택 시간이 종료되기 전에 저장해주세요.
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">선택 저장하기</button>
                    </form>

                    <?php if (!empty($my_selections)): ?>
                        <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
                            <h3>현재 내 선택</h3>
                            <?php foreach ($my_selections as $pref => $selected_id): ?>
                                <?php
                                $selected_user = null;
                                foreach ($participants as $p) {
                                    if ($p['id'] == $selected_id) {
                                        $selected_user = $p;
                                        break;
                                    }
                                }
                                if ($selected_user):
                                ?>
                                    <p>
                                        <strong><?= $pref ?>지망:</strong>
                                        [ID: <?= $selected_user['id'] ?>] <?= escape($selected_user['name']) ?>
                                        (<?= calculateAge($selected_user['birth_date']) ?>세)
                                    </p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // 중복 선택 방지
            $('#selectionForm').on('submit', function(e) {
                var first = $('#first_choice').val();
                var second = $('#second_choice').val();
                var third = $('#third_choice').val();

                var selected = [first, second, third].filter(function(v) { return v && v !== ''; });
                var unique = [...new Set(selected)];

                if (selected.length !== unique.length) {
                    e.preventDefault();
                    alert('같은 사람을 중복으로 선택할 수 없습니다.');
                    return false;
                }
            });

            // 선택 시 다른 드롭다운에서 비활성화
            $('.selection-dropdown').on('change', function() {
                var selectedValues = [];
                $('.selection-dropdown').each(function() {
                    var val = $(this).val();
                    if (val) selectedValues.push(val);
                });

                $('.selection-dropdown option').each(function() {
                    var $option = $(this);
                    var val = $option.val();
                    if (val && selectedValues.includes(val) && $option.parent().val() !== val) {
                        $option.prop('disabled', true);
                    } else if (val) {
                        $option.prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>
