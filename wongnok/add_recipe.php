
<?php
session_start();
require_once 'wongnok/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $difficulty = $_POST['difficulty'];
  $cook_time = $_POST['cook_time'];
  $user_id = $_SESSION['user_id'];

  $imagePath = '';
  if ($_FILES['image']['name']) {
    $targetDir = "uploads/";
    $imageName = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $imageName;
    move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    $imagePath = $targetFile;
  }

  $stmt = $conn->prepare("INSERT INTO recipes (user_id, title, description, difficulty, cook_time, image) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isssis", $user_id, $title, $description, $difficulty, $cook_time, $imagePath);
  $stmt->execute();

  header("Location: index.php");
  exit();
}
?>