<?php
include __DIR__ . '/../db_connect.php';
session_start();

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(["success" => false]);
    exit;
}

$userId = $_SESSION['user_id'];

$owner = $_POST['owner'];
$history = $_POST['history'];
$vision = $_POST['vision'];
$mission = $_POST['mission'];

$imgPath = null;

// Get current image path from DB
$stmtCheck = $const->prepare("SELECT shop_img_path FROM shop WHERE shop_id=?");
$stmtCheck->bind_param("i", $userId);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
$currentData = $result->fetch_assoc();
$currentImg = $currentData['shop_img_path'] ?? null;

if (!empty($_FILES['profile_img']['name'])) {
    $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array(strtolower($ext), $allowed)) {
        echo json_encode(["success" => false, "msg" => "Invalid image"]);
        exit;
    }

    $newName = "profile_" . $userId . "_" . time() . "." . $ext;
    $uploadDir = "../profile_img/";
    $targetPath = $uploadDir . $newName;

    if (!move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetPath)) {
        echo json_encode(["success" => false]);
        exit;
    }

    $imgPath = "profile_img/" . $newName;

    // Delete old image if it exists
    if ($currentImg && file_exists("../" . $currentImg)) {
        unlink("../" . $currentImg);
    }
}

if ($imgPath) {
    $stmt = $const->prepare(
        "UPDATE shop 
         SET shop_owner=?, shop_history=?, shop_vision=?, shop_mission=?, shop_img_path=?
         WHERE shop_id=?"
    );
    $stmt->bind_param("sssssi", $owner, $history, $vision, $mission, $imgPath, $userId);
} else {
    $stmt = $const->prepare(
        "UPDATE shop 
         SET shop_owner=?, shop_history=?, shop_vision=?, shop_mission=?
         WHERE shop_id=?"
    );
    $stmt->bind_param("ssssi", $owner, $history, $vision, $mission, $userId);
}

$stmt->execute();

$shop = $const->prepare("SELECT t1.*, t2.user_description FROM shop AS t1 JOIN users AS t2 ON t1.shop_id = t2.user_id WHERE user_id = ?");
$shop->bind_param("i", $_SESSION["user_id"]);
$shop->execute();
$shopResult = $shop->get_result()->fetch_assoc();

$defImg = 'profile_img/default.jpg';

echo json_encode(["success" => true, "info" => $shopResult]);
