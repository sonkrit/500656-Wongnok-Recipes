<?php
session_start();
require_once 'config/db.php';

if (!isset($_GET['id'])) {
    echo "ไม่พบเมนูที่คุณเลือก";
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "ไม่พบเมนูนี้ในระบบ";
    exit();
}

$recipe = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($recipe['title']) ?> | รายละเอียดเมนู</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5 text-center">
  <h1 class="mb-4"><?= htmlspecialchars($recipe['title']) ?></h1>

  <?php if (!empty($recipe['image'])): ?>
    <img src="<?= htmlspecialchars($recipe['image']) ?>" alt="รูปภาพเมนู" class="img-fluid mb-4" style="max-width: 400px;">
  <?php endif; ?>
    
  <p><strong>รายละเอียด:</strong><br><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
  <p><strong>ระดับความยาก:</strong> <?= htmlspecialchars($recipe['difficulty']) ?></p>
  <p><strong>เวลาในการทำ:</strong> <?= htmlspecialchars($recipe['cook_time']) ?> นาที</p>
  <!-- คะแนนเฉลี่ย -->
<?php
$stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating FROM ratings WHERE recipe_id = ?");
$stmt->bind_param("i", $recipe['id']);
$stmt->execute();
$avg = $stmt->get_result()->fetch_assoc();
echo "<p><strong>คะแนนเฉลี่ย:</strong> " . round($avg['avg_rating'], 1) . " / 5</p>";
?>

<!-- ฟอร์มให้คะแนน ถ้าไม่ใช่เจ้าของ -->
<?php if ($_SESSION['user_id'] != $recipe['user_id']): ?>
  <form action="rate_recipe.php" method="POST">
    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
    <label for="rating">ให้คะแนน:</label>
    <select name="rating" required>
      <option value="5">⭐⭐⭐⭐⭐</option>
      <option value="4">⭐⭐⭐⭐</option>
      <option value="3">⭐⭐⭐</option>
      <option value="2">⭐⭐</option>
      <option value="1">⭐</option>
    </select>
    <button type="submit" class="btn btn-sm btn-primary">ส่งคะแนน</button>
  </form>
<?php endif; ?>
  

  <a href="index.php" class="btn btn-secondary mt-4">← กลับหน้าเมนูทั้งหมด</a>
</div>

</body>
</html>
