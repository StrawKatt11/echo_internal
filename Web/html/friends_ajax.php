<?php
require '../database/db.php';
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) exit(json_encode([]));
$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch($action) {

  case 'list':
    $stmt = $pdo->prepare("
      SELECT u.id, u.username, f.id AS friend_row_id
      FROM users u 
      JOIN friends f ON ((f.user_id = ? AND f.friend_id = u.id) OR (f.friend_id = ? AND f.user_id = u.id)) 
      WHERE f.status='accepted'
    ");
    $stmt->execute([$user_id,$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case 'requests':
    $stmt = $pdo->prepare("
      SELECT f.id, u.username
      FROM friends f
      JOIN users u ON u.id = f.user_id
      WHERE f.friend_id=? AND f.status='pending'
    ");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case 'add':
    $username = trim($input['username'] ?? '');
    if(!$username) exit(json_encode(['success'=>false,'message'=>'No username']));

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username=?");
    $stmt->execute([$username]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$friend) exit(json_encode(['success'=>false,'message'=>'User not found']));
    $friend_id = $friend['id'];

    if($friend_id == $user_id) exit(json_encode(['success'=>false,'message'=>"Can't add yourself"]));

    $stmt = $pdo->prepare("SELECT id FROM friends WHERE (user_id=? AND friend_id=?) OR (user_id=? AND friend_id=?)");
    $stmt->execute([$user_id,$friend_id,$friend_id,$user_id]);
    if($stmt->rowCount()>0) exit(json_encode(['success'=>false,'message'=>'Already friends or pending']));

    $stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?,?)");
    $stmt->execute([$user_id,$friend_id]);
    echo json_encode(['success'=>true,'message'=>'Friend request sent']);
    break;

  case 'accept':
    $id = intval($input['id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE friends SET status='accepted' WHERE id=? AND friend_id=?");
    $stmt->execute([$id,$user_id]);
    echo json_encode(['success'=>true,'message'=>'Friend request accepted']);
    break;

  case 'decline':
    $id = intval($input['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM friends WHERE id=? AND friend_id=?");
    $stmt->execute([$id,$user_id]);
    echo json_encode(['success'=>true,'message'=>'Friend request declined']);
    break;

  case 'remove':
    $id = intval($input['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM friends WHERE id=? AND (user_id=? OR friend_id=?)");
    $stmt->execute([$id,$user_id,$user_id]);
    echo json_encode(['success'=>true,'message'=>'Friend removed']);
    break;

  default:
    echo json_encode([]);
}
