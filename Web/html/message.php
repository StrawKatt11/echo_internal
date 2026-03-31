<?php
require '../database/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not logged in']));
}

$me = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'load') {
    $other = (int)($_GET['user'] ?? 0);
    if ($other <= 0) {
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id, sender_id, message, created_at
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    $stmt->execute([$me, $other, $other, $me]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action === 'send') {
    $data = json_decode(file_get_contents("php://input"), true);
    $msg = trim($data['message'] ?? '');
    $other = (int)($data['user'] ?? 0);

    if ($msg === '' || $other <= 0) {
        echo json_encode(['error' => 'Invalid message or recipient']);
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $check->execute([$other]);
    if (!$check->fetch()) {
        echo json_encode(['error' => 'User does not exist']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$me, $other, $msg]);

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete') {
    $msg_id = (int)($_POST['id'] ?? 0);
    if (!$msg_id) {
        echo json_encode(['error' => 'Message ID missing']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT sender_id FROM messages WHERE id = ?");
    $stmt->execute([$msg_id]);
    $sender_id = $stmt->fetchColumn();

    if (!$sender_id || $sender_id != $me) {
        echo json_encode(['error' => 'Permission denied']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$msg_id]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);