<?php
include __DIR__ . '/../db_connect.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../Signin.php");
    exit();
}

$book_id = json_decode(file_get_contents('php://input'), true)['book_id'];
$save_quantity = $_POST['save_quantity'] ?? 1;

$stmt = $const->prepare("INSERT INTO save_books (user_id, book_id, save_quantity) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $_SESSION['user_id'], $book_id, $save_quantity);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

?>