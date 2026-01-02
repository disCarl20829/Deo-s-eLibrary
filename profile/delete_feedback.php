<?php
include __DIR__ . '/../db_connect.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$userId = $_SESSION['user_id'] ?? 0;

// Get feedback owner
$stmt = $const->prepare(
    "SELECT user_id FROM customer_feedbacks WHERE feedback_id = ?"
);
$stmt->bind_param("i", $data['feedback_id']);
$stmt->execute();
$owner = $stmt->get_result()->fetch_assoc();

if (!$owner || $owner['user_id'] != $userId) {
    $shop = $const->query("SELECT shop_id FROM shop LIMIT 1")->fetch_assoc();
    if ($shop['shop_id'] != $userId) {
        echo json_encode(['success' => false]);
        exit;
    }
}

$update = $const->prepare(
    "DELETE FROM customer_feedbacks
     WHERE feedback_id = ?"
);

$update->bind_param(
    "i",
    $data['feedback_id']
);

$update->execute();
echo json_encode(['success' => true]);
