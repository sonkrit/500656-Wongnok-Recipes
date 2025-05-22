<?php
include 'config/db.php';

$keyword = $_GET['keyword'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$max_time = $_GET['time'] ?? '';

$sql = "SELECT * FROM recipes WHERE 1=1";
$params = [];
$types = "";

// คำค้นชื่อเมนู
if (!empty($keyword)) {
  $sql .= " AND title LIKE ?";
  $params[] = "%$keyword%";
  $types .= "s";
}

// ความยาก
if (!empty($difficulty)) {
  $sql .= " AND difficulty = ?";
  $params[] = $difficulty;
  $types .= "s";
}

// เวลา
if (!empty($max_time)) {
  $sql .= " AND time_required <= ?";
  $params[] = (int)$max_time;
  $types .= "i";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// แสดงผลลัพธ์เป็นการ์ด Bootstrap เหมือนในหน้าแรก
?>
<div class="container mt-5">
  <h3 class="mb-4">ผลการค้นหา</h3>
  <div class="row g-4">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="col-md-4">
        <div class="card recipe-card shadow-sm">
          <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="ภาพอาหาร">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
            <p class="card-text">
              <span class="badge bg-<?= $row['difficulty'] == 'ง่าย' ? 'info' : ($row['difficulty'] == 'ปานกลาง' ? 'success' : 'danger') ?>">
                <?= htmlspecialchars($row['difficulty']) ?>
              </span>
              <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['time_required']) ?> นาที</span>
            </p>
            <a href="recipe_detail.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">ดูรายละเอียด</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>