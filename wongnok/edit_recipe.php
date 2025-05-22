<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$id = $_GET['id'] ?? 0;

// ตรวจสอบว่าเมนูนี้เป็นของผู้ใช้คนนี้จริง
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe) {
  echo "ไม่พบเมนู หรือคุณไม่มีสิทธิ์แก้ไข";
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $difficulty = $_POST['difficulty'];
  $cook_time = $_POST['cook_time'];

  $imagePath = $recipe['image'];
  if ($_FILES['image']['name']) {
    $targetDir = "uploads/";
    $imageName = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $imageName;
    move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    $imagePath = $targetFile;
  }

  $stmt = $conn->prepare("UPDATE recipes SET title=?, description=?, difficulty=?, cook_time=?, image=? WHERE id=? AND user_id=?");
  $stmt->bind_param("sssissi", $title, $description, $difficulty, $cook_time, $imagePath, $id, $_SESSION['user_id']);
  $stmt->execute();

  header("Location: recipe_detail.php?id=$id");
  exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>แก้ไขเมนู</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">แก้ไขเมนู</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">ชื่อเมนู</label>
      <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">รายละเอียด</label>
      <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($recipe['description']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">ความยาก</label>
      <select name="difficulty" class="form-select">
        <option value="ง่าย" <?= $recipe['difficulty'] == 'ง่าย' ? 'selected' : '' ?>>ง่าย</option>
        <option value="ปานกลาง" <?= $recipe['difficulty'] == 'ปานกลาง' ? 'selected' : '' ?>>ปานกลาง</option>
        <option value="ยาก" <?= $recipe['difficulty'] == 'ยาก' ? 'selected' : '' ?>>ยาก</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="cook_time" class="form-label">เวลาทำ (นาที)</label>
      <input type="number" class="form-control" name="cook_time" value="<?= $recipe['cook_time'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="image" class="form-label">รูปภาพ (อัปโหลดใหม่หากต้องการเปลี่ยน)</label><br>
      <img src="<?= $recipe['image'] ?>" width="200" class="mb-2"><br>
      <input type="file" name="image" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
    <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
  </form>
</div>
</body>
</html>