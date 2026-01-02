<?php
include __DIR__ . '/../db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $const->prepare("
    INSERT INTO customer_feedbacks (user_id, feedback_message, feedback_rating, feedback_date)
    VALUES (?, ?, ?, NOW())
");

$stmt->bind_param(
    "isi",
    $_SESSION['user_id'],
    $data['message'],
    $data['rating']
);

$stmt->execute();

echo json_encode(['success' => true]);
