<?php
session_start();
require_once 'config/db.php';

$recipe_id = $_POST['recipe_id'];
$rating = $_POST['rating'];
$user_id = $_SESSION['user_id'];

// ห้ามให้คะแนนเมนูของตัวเอง
$stmt = $conn->prepare("SELECT user_id FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if ($recipe['user_id'] == $user_id) {
  exit("ห้ามให้คะแนนเมนูของตัวเอง");
}

$stmt = $conn->prepare("REPLACE INTO ratings (user_id, recipe_id, rating) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $user_id, $recipe_id, $rating);
$stmt->execute();

header("Location: recipe_detail.php?id=$recipe_id");
exit();
?>
