<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();

header("Location: index.php");
exit();
?>
