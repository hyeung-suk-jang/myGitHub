<?php
/**
 * 실시간 채팅 API
 * WebSocket 대신 AJAX Long Polling 방식 사용
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-rooms':
        getRooms($db);
        break;

    case 'get-or-create-room':
        if ($method === 'POST') {
            getOrCreateRoom($db, $input);
        }
        break;

    case 'get-messages':
        getMessages($db);
        break;

    case 'send-message':
        if ($method === 'POST') {
            sendMessage($db, $input);
        }
        break;

    case 'mark-as-read':
        if ($method === 'PUT') {
            markAsRead($db, $input);
        }
        break;

    case 'poll-messages':
        pollMessages($db);
        break;

    case 'upload-attachment':
        if ($method === 'POST') {
            uploadAttachment($db);
        }
        break;

    case 'get-unread-count':
        getUnreadCount($db);
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 채팅방 목록 조회
 */
function getRooms($db) {
    $userId = requireAuth();

    try {
        $stmt = $db->prepare("
            SELECT
                cr.*,
                IF(cr.user1_id = ?, u2.username, u1.username) as counterpart_name,
                IF(cr.user1_id = ?, u2.user_id, u1.user_id) as counterpart_id,
                r.equipment_id,
                e.equipment_name,
                (SELECT COUNT(*) FROM chat_messages cm
                 WHERE cm.room_id = cr.room_id
                 AND cm.sender_id != ?
                 AND cm.is_read = FALSE) as unread_count,
                (SELECT message FROM chat_messages cm
                 WHERE cm.room_id = cr.room_id
                 ORDER BY cm.created_at DESC LIMIT 1) as last_message
            FROM chat_rooms cr
            LEFT JOIN users u1 ON cr.user1_id = u1.user_id
            LEFT JOIN users u2 ON cr.user2_id = u2.user_id
            LEFT JOIN rentals r ON cr.rental_id = r.rental_id
            LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
            WHERE cr.user1_id = ? OR cr.user2_id = ?
            ORDER BY cr.last_message_at DESC, cr.created_at DESC
        ");

        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        $rooms = $stmt->fetchAll();

        sendSuccess(['rooms' => $rooms]);

    } catch (Exception $e) {
        logError('Get rooms error: ' . $e->getMessage());
        sendError('Failed to get chat rooms', 500);
    }
}

/**
 * 채팅방 조회 또는 생성
 */
function getOrCreateRoom($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        $otherUserId = $input['other_user_id'] ?? null;

        if (!$rentalId || !$otherUserId) {
            sendError('Rental ID and other user ID required', 400);
        }

        // 대여 정보 확인
        $stmt = $db->prepare("
            SELECT owner_id, renter_id
            FROM rentals
            WHERE rental_id = ?
        ");
        $stmt->execute([$rentalId]);
        $rental = $stmt->fetch();

        if (!$rental) {
            sendError('Rental not found', 404);
        }

        // 권한 확인 (대여 관련 당사자만 채팅 가능)
        if ($rental['owner_id'] != $userId && $rental['renter_id'] != $userId) {
            sendError('Unauthorized', 403);
        }

        $db->beginTransaction();

        // 기존 채팅방 확인
        $stmt = $db->prepare("
            SELECT room_id
            FROM chat_rooms
            WHERE rental_id = ?
            AND ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
        ");
        $stmt->execute([$rentalId, $userId, $otherUserId, $otherUserId, $userId]);
        $room = $stmt->fetch();

        if ($room) {
            $roomId = $room['room_id'];
        } else {
            // 새 채팅방 생성
            $stmt = $db->prepare("
                INSERT INTO chat_rooms (rental_id, user1_id, user2_id)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$rentalId, $userId, $otherUserId]);
            $roomId = $db->lastInsertId();
        }

        $db->commit();

        sendSuccess([
            'room_id' => $roomId,
            'rental_id' => $rentalId
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        logError('Get or create room error: ' . $e->getMessage());
        sendError('Failed to create chat room', 500);
    }
}

/**
 * 메시지 조회
 */
function getMessages($db) {
    $userId = requireAuth();

    try {
        $roomId = $_GET['room_id'] ?? null;
        $limit = min(100, max(1, intval($_GET['limit'] ?? 50)));
        $offset = max(0, intval($_GET['offset'] ?? 0));

        if (!$roomId) {
            sendError('Room ID required', 400);
        }

        // 채팅방 접근 권한 확인
        $stmt = $db->prepare("
            SELECT room_id
            FROM chat_rooms
            WHERE room_id = ? AND (user1_id = ? OR user2_id = ?)
        ");
        $stmt->execute([$roomId, $userId, $userId]);
        if (!$stmt->fetch()) {
            sendError('Unauthorized', 403);
        }

        // 메시지 조회
        $stmt = $db->prepare("
            SELECT
                cm.*,
                u.username as sender_name,
                (SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'attachment_id', ca.attachment_id,
                        'file_path', ca.file_path,
                        'file_name', ca.file_name,
                        'file_size', ca.file_size,
                        'file_type', ca.file_type
                    )
                ) FROM chat_attachments ca WHERE ca.message_id = cm.message_id) as attachments
            FROM chat_messages cm
            LEFT JOIN users u ON cm.sender_id = u.user_id
            WHERE cm.room_id = ?
            ORDER BY cm.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$roomId, $limit, $offset]);
        $messages = $stmt->fetchAll();

        // 역순으로 정렬 (오래된 메시지가 먼저)
        $messages = array_reverse($messages);

        sendSuccess(['messages' => $messages]);

    } catch (Exception $e) {
        logError('Get messages error: ' . $e->getMessage());
        sendError('Failed to get messages', 500);
    }
}

/**
 * 메시지 전송
 */
function sendMessage($db, $input) {
    $userId = requireAuth();

    try {
        $roomId = $input['room_id'] ?? null;
        $message = trim($input['message'] ?? '');

        if (!$roomId || !$message) {
            sendError('Room ID and message required', 400);
        }

        // 채팅방 접근 권한 확인
        $stmt = $db->prepare("
            SELECT room_id, user1_id, user2_id
            FROM chat_rooms
            WHERE room_id = ? AND (user1_id = ? OR user2_id = ?)
        ");
        $stmt->execute([$roomId, $userId, $userId]);
        $room = $stmt->fetch();

        if (!$room) {
            sendError('Unauthorized', 403);
        }

        $db->beginTransaction();

        // 메시지 저장
        $stmt = $db->prepare("
            INSERT INTO chat_messages (room_id, sender_id, message)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$roomId, $userId, $message]);
        $messageId = $db->lastInsertId();

        // 채팅방 최근 메시지 시간 업데이트
        $stmt = $db->prepare("
            UPDATE chat_rooms
            SET last_message_at = NOW()
            WHERE room_id = ?
        ");
        $stmt->execute([$roomId]);

        // 상대방에게 알림 생성
        $recipientId = $room['user1_id'] == $userId ? $room['user2_id'] : $room['user1_id'];
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '새 메시지', ?, 'chat', ?)
        ");
        $stmt->execute([$recipientId, substr($message, 0, 50), $messageId]);

        $db->commit();

        // 방금 전송한 메시지 정보 반환
        $stmt = $db->prepare("
            SELECT
                cm.*,
                u.username as sender_name
            FROM chat_messages cm
            LEFT JOIN users u ON cm.sender_id = u.user_id
            WHERE cm.message_id = ?
        ");
        $stmt->execute([$messageId]);
        $sentMessage = $stmt->fetch();

        sendSuccess(['message' => $sentMessage]);

    } catch (Exception $e) {
        $db->rollBack();
        logError('Send message error: ' . $e->getMessage());
        sendError('Failed to send message', 500);
    }
}

/**
 * 메시지 읽음 처리
 */
function markAsRead($db, $input) {
    $userId = requireAuth();

    try {
        $roomId = $input['room_id'] ?? null;

        if (!$roomId) {
            sendError('Room ID required', 400);
        }

        // 채팅방 접근 권한 확인
        $stmt = $db->prepare("
            SELECT room_id
            FROM chat_rooms
            WHERE room_id = ? AND (user1_id = ? OR user2_id = ?)
        ");
        $stmt->execute([$roomId, $userId, $userId]);
        if (!$stmt->fetch()) {
            sendError('Unauthorized', 403);
        }

        // 읽지 않은 메시지를 읽음으로 표시
        $stmt = $db->prepare("
            UPDATE chat_messages
            SET is_read = TRUE
            WHERE room_id = ? AND sender_id != ? AND is_read = FALSE
        ");
        $stmt->execute([$roomId, $userId]);

        sendSuccess(['updated' => $stmt->rowCount()]);

    } catch (Exception $e) {
        logError('Mark as read error: ' . $e->getMessage());
        sendError('Failed to mark messages as read', 500);
    }
}

/**
 * Long Polling - 새 메시지 확인
 */
function pollMessages($db) {
    $userId = requireAuth();

    try {
        $roomId = $_GET['room_id'] ?? null;
        $lastMessageId = intval($_GET['last_message_id'] ?? 0);
        $timeout = min(30, max(5, intval($_GET['timeout'] ?? 20))); // 5-30초

        if (!$roomId) {
            sendError('Room ID required', 400);
        }

        // 채팅방 접근 권한 확인
        $stmt = $db->prepare("
            SELECT room_id
            FROM chat_rooms
            WHERE room_id = ? AND (user1_id = ? OR user2_id = ?)
        ");
        $stmt->execute([$roomId, $userId, $userId]);
        if (!$stmt->fetch()) {
            sendError('Unauthorized', 403);
        }

        $startTime = time();
        $newMessages = [];

        // Long polling 루프
        while (time() - $startTime < $timeout) {
            // 새 메시지 확인
            $stmt = $db->prepare("
                SELECT
                    cm.*,
                    u.username as sender_name
                FROM chat_messages cm
                LEFT JOIN users u ON cm.sender_id = u.user_id
                WHERE cm.room_id = ? AND cm.message_id > ?
                ORDER BY cm.created_at ASC
            ");
            $stmt->execute([$roomId, $lastMessageId]);
            $newMessages = $stmt->fetchAll();

            if (!empty($newMessages)) {
                break;
            }

            // 1초 대기
            sleep(1);
        }

        sendSuccess([
            'messages' => $newMessages,
            'has_new' => !empty($newMessages)
        ]);

    } catch (Exception $e) {
        logError('Poll messages error: ' . $e->getMessage());
        sendError('Failed to poll messages', 500);
    }
}

/**
 * 파일 첨부
 */
function uploadAttachment($db) {
    $userId = requireAuth();

    try {
        $messageId = $_POST['message_id'] ?? null;

        if (!$messageId) {
            sendError('Message ID required', 400);
        }

        // 메시지 권한 확인
        $stmt = $db->prepare("
            SELECT cm.message_id
            FROM chat_messages cm
            INNER JOIN chat_rooms cr ON cm.room_id = cr.room_id
            WHERE cm.message_id = ?
            AND (cr.user1_id = ? OR cr.user2_id = ?)
            AND cm.sender_id = ?
        ");
        $stmt->execute([$messageId, $userId, $userId, $userId]);
        if (!$stmt->fetch()) {
            sendError('Unauthorized', 403);
        }

        if (!isset($_FILES['file'])) {
            sendError('File required', 400);
        }

        $filePath = uploadFile($_FILES['file'], 'chat');
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        $stmt = $db->prepare("
            INSERT INTO chat_attachments (message_id, file_path, file_name, file_size, file_type)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$messageId, $filePath, $fileName, $fileSize, $fileType]);

        sendSuccess([
            'attachment_id' => $db->lastInsertId(),
            'file_path' => $filePath
        ]);

    } catch (Exception $e) {
        logError('Upload attachment error: ' . $e->getMessage());
        sendError('Failed to upload file: ' . $e->getMessage(), 500);
    }
}

/**
 * 읽지 않은 메시지 개수 조회
 */
function getUnreadCount($db) {
    $userId = requireAuth();

    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) as unread_count
            FROM chat_messages cm
            INNER JOIN chat_rooms cr ON cm.room_id = cr.room_id
            WHERE (cr.user1_id = ? OR cr.user2_id = ?)
            AND cm.sender_id != ?
            AND cm.is_read = FALSE
        ");
        $stmt->execute([$userId, $userId, $userId]);
        $result = $stmt->fetch();

        sendSuccess(['unread_count' => intval($result['unread_count'])]);

    } catch (Exception $e) {
        logError('Get unread count error: ' . $e->getMessage());
        sendError('Failed to get unread count', 500);
    }
}
