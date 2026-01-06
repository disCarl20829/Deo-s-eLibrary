<?php
include __DIR__ . '/../db_connect.php';

session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../Signin.php");
    exit();
}

$oldBook = $_POST['old_book_img_path'] ?? '';

if (isset($_FILES['book_img_path'])) {
    $image = $_FILES['book_img_path'];

    $oldFile = __DIR__ . "/books/" . basename($oldBook);

    if (file_exists($oldFile)) {
        unlink($oldFile);
    }

    $uploadDir = "books/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($image["name"]);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($image["tmp_name"], $filePath)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        exit;
    }

    $dbFilePath = "funcs/php/crud/books/" . $fileName;
} else {
    $dbFilePath = $oldBook;
}

$book_id = $_POST['book_id'] ?? '';

$stmt = $const->prepare("UPDATE books SET book_title = ?, book_author = ?, book_pubdate = ?, book_description = ?, book_price = ?, book_img_path = ? WHERE book_id = ?");
$stmt->bind_param("ssssdsi", $_POST['book_title'], $_POST['book_author'], $_POST['book_pubdate'], $_POST['book_description'], $_POST['book_price'], $dbFilePath, $book_id);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error updating: " . $stmt->error]);
    exit;
}

$category_id = $_POST["book_category"] ?? '';

$stmt2 = $const->prepare("UPDATE book_category SET category_id = ? WHERE book_id = ?");
$stmt2->bind_param("ii", $category_id, $book_id);

if (!$stmt2->execute()) {
    echo json_encode(["status" => "error", "message" => "Error updating category: " . $stmt2->error]);
    exit;
}

echo json_encode(['success' => true]);
