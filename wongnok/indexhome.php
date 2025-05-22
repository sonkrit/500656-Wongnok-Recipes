<?php
session_start();
include 'config/db.php';
$user_id = $_SESSION['user_id'] ?? null;

$keyword = $_GET['keyword'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$max_time = $_GET['time'] ?? '';

$sql = "SELECT r.*, 
       (SELECT rating FROM ratings WHERE user_id = ? AND recipe_id = r.id) AS my_rating,
       (SELECT ROUND(AVG(rating), 1) FROM ratings WHERE recipe_id = r.id) AS avg_rating
        FROM recipes r WHERE 1=1";

$params = [$user_id];
$types = "i";

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
if ($stmt === false) {
  die("Prepare failed: " . $conn->error);
}

if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
  die("ไม่สามารถดึงข้อมูลได้: " . $stmt->error);
}
?>

<!DOCTYPE html>

<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Wongnok รวมสูตรอาหาร</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .recipe-card img {
      height: 200px;
      object-fit: cover;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  
  <div class="container">



    <a class="navbar-brand" href="#">Wongnok</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <!--li class="nav-item"><a class="nav-link" href="#">หน้าแรก</a></li-->
        <!--li class="nav-item"><a class="nav-link" href="search.php">ค้นหา</a></li-->
        <li class="nav-item"><a class="nav-link" href="login.html">เข้าสู่ระบบ</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Header -->
<header class="bg-light py-5 text-center">

<!-- ฟอร์มค้นหา -->
<div class="container mt-4">
  <div class="card p-4 shadow-sm">
    <form class="row g-3" method="GET" action="index.php">
      <div class="col-md-4">
        <input type="text" name="keyword" class="form-control" placeholder="ค้นหาชื่อเมนู..." value="<?= htmlspecialchars($keyword) ?>">
      </div>
      <div class="col-md-3">
        <select name="difficulty" class="form-select">
          <option value="">-- เลือกระดับความยาก --</option>
          <option value="ง่าย" <?= $difficulty == 'ง่าย' ? 'selected' : '' ?>>ง่าย</option>
          <option value="ปานกลาง" <?= $difficulty == 'ปานกลาง' ? 'selected' : '' ?>>ปานกลาง</option>
          <option value="ยาก" <?= $difficulty == 'ยาก' ? 'selected' : '' ?>>ยาก</option>
        </select>
      </div>
      <div class="col-md-3">
        <input type="number" name="time" class="form-control" placeholder="เวลาทำไม่เกิน (นาที)" value="<?= htmlspecialchars($max_time) ?>">
      </div>
     <div class="col-md-2 d-grid gap-2">
  <button type="submit" class="btn btn-primary">ค้นหา</button>
  <button type="button" class="btn btn-secondary" onclick="resetSearch()">รีเซ็ต</button>
</div>
      
    </form>
  </div>
</div><br>


  <div class="container">
    <h1 class="display-5 fw-bold">รวมสูตรอาหารจากสมาชิกทั่วไทย</h1>
    <p class="lead">ค้นหา แบ่งปัน และเรียนรู้สูตรเด็ดจานโปรดของคุณ</p>
  </div>
</header>

<!-- เมนูอาหาร -->
<div class="container mt-5">
 

 <div class="row g-4 mt-3">
  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="col-md-4">
      <div class="card recipe-card shadow-sm">
        <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
          <p class="card-text">
            <span class="badge bg-<?= $row['difficulty'] == 'ง่าย' ? 'info' : ($row['difficulty'] == 'ปานกลาง' ? 'success' : 'danger') ?>">
              <?= htmlspecialchars($row['difficulty']) ?>
            </span>
            <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['cook_time']) ?> นาที</span>
          </p>
          
          <!--p class="card-text">⭐ <?= $row['avg_rating'] ?? 'ยังไม่มีคะแนน' ?></p-->
          <a href="recipe_detail.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">ดูรายละเอียด</a>
          <p class="card-text">⭐ คะแนนของคุณ: <?= $row['my_rating'] ?? 'ยังไม่ได้ให้คะแนน' ?></p>
          <p class="card-text">🌟 คะแนนเฉลี่ย: <?= $row['avg_rating'] ?? 'ยังไม่มีคะแนน' ?></p>
          
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>


<!-- Footer -->
<footer class="bg-dark text-white mt-5 p-3 text-center">
  © 2025 รวมสูตรอาหาร | พัฒนาโดยทีม DevChef
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function resetSearch() {
  const form = document.querySelector('form');
  
  // ล้างค่า input ทั้งหมด
  form.keyword.value = '';
  form.difficulty.value = '';
  form.time.value = '';

  // ส่งแบบฟอร์มใหม่ (reload หน้าโดยไม่มี query string)
  window.location.href = window.location.pathname;
}
</script>
</body>
</html>