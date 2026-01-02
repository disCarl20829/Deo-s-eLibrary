<?php
include __DIR__ . '/../db_connect.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $const->prepare(
    "DELETE FROM save_books WHERE user_id = ? AND book_id = ?"
);
$stmt->bind_param("ii", $_SESSION['user_id'], $data['book_id']);
$stmt->execute();

echo json_encode(['success' => true]);