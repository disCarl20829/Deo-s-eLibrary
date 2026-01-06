<?php
include __DIR__ . '/../db_connect.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../Signin.php");
    exit();
}

$book_id = json_decode(file_get_contents('php://input'), true)['book_id'];

$stmt = $const->prepare("DELETE FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>