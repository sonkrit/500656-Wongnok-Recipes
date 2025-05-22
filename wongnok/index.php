<?php
session_start();
include 'config/db.php';

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: index.php");
  exit;
}

$search = $_GET['search'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$cook_time = $_GET['cook_time'] ?? '';

$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
  $where .= " AND r.title LIKE ?";
  $params[] = "%$search%";
}
if (!empty($difficulty)) {
  $where .= " AND r.difficulty = ?";
  $params[] = $difficulty;
}
if (!empty($cook_time)) {
  $where .= " AND r.cook_time <= ?";
  $params[] = $cook_time;
}

$sql = "SELECT r.*, 
        (SELECT ROUND(AVG(rating),1) FROM ratings WHERE recipe_id = r.id) AS avg_rating
        FROM recipes r
        $where
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $types = str_repeat('s', count($params));
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>สูตรอาหารทั้งหมด</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .recipe-card img {
      height: 200px;
      object-fit: cover;
      border-top-left-radius: 0.5rem;
      border-top-right-radius: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
      <h2 class="text-center text-md-start mb-3 mb-md-0">🍳 สูตรอาหารล่าสุด</h2>
      <div class="text-center text-md-end">
        <?php if (isset($_SESSION['username'])): ?>
          <span class="me-2">👋 สวัสดี, <?= $_SESSION['username'] ?></span>
          <a href="?logout=true" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a>
          <a href="add_recipe.html" class="btn btn-success btn-sm">+ เพิ่มเมนู</a>
        <?php else: ?>
          <a href="login.html" class="btn btn-outline-primary btn-sm">เข้าสู่ระบบ</a>
        <?php endif; ?>
      </div>
      <a class="btn btn-danger btn-sm" href="indexhome.php">Home</a>
    </div>

    <!-- Search Filter -->
    <form class="row g-2 align-items-end mb-4" method="GET">
      <div class="col-md-4">
        <label for="search" class="form-label">ค้นหาด้วยชื่อเมนู</label>
        <input type="text" id="search" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-3">
        <label for="difficulty" class="form-label">ระดับความยาก</label>
        <select name="difficulty" id="difficulty" class="form-select">
          <option value="">-- ทั้งหมด --</option>
          <option value="ง่าย" <?= $difficulty == 'ง่าย' ? 'selected' : '' ?>>ง่าย</option>
          <option value="ปานกลาง" <?= $difficulty == 'ปานกลาง' ? 'selected' : '' ?>>ปานกลาง</option>
          <option value="ยาก" <?= $difficulty == 'ยาก' ? 'selected' : '' ?>>ยาก</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="cook_time" class="form-label">เวลาทำ (นาที)</label>
        <input type="number" name="cook_time" id="cook_time" class="form-control" value="<?= htmlspecialchars($cook_time) ?>">
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-primary" type="submit">ค้นหา</button>
        <a href="index.php" class="btn btn-secondary mt-2">รีเซ็ต</a>
      </div>
    </form>

    <!-- Recipes Grid -->
    <div class="row">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
          <div class="card recipe-card h-100 shadow">
            <?php if (!empty($row['image'])): ?>
              <img src="<?= htmlspecialchars($row['image']) ?>" alt="รูปเมนู" class="card-img-top">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
              <p class="mb-1">
                <span class="badge bg-info"><?= htmlspecialchars($row['difficulty']) ?></span>
                <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['cook_time']) ?> นาที</span>
              </p>
              <p class="mb-2">⭐ คะแนนเฉลี่ย: <?= $row['avg_rating'] ?? 'ยังไม่มี' ?></p>
              <div class="mt-auto">
                <a href="recipe_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">ดูรายละเอียด</a>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                  <a href="edit_recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                  <a href="delete_recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบเมนูนี้?')">ลบ</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
