<?php
include __DIR__ . '/../db_connect.php';
session_start();

$currentUser = $_SESSION['user_id'] ?? 0;

$ownerRes = $const->query("SELECT shop_id FROM shop LIMIT 1");
$owner = $ownerRes->fetch_assoc();
$shopOwnerId = $owner['shop_id'];

$sql = "
    SELECT 
        f.feedback_id,
        f.user_id,
        f.feedback_message,
        f.feedback_rating,
        u.user_name
    FROM customer_feedbacks f
    JOIN users u ON f.user_id = u.user_id
    ORDER BY f.feedback_date DESC
";

$result = $const->query($sql);
$feedbacks = [];

while ($row = $result->fetch_assoc()) {
    $row['can_edit'] = (
        $row['user_id'] == $currentUser ||
        $currentUser == $shopOwnerId
    );
    $feedbacks[] = $row;
}

echo json_encode($feedbacks);
