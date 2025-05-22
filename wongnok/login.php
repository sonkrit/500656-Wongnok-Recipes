<?php
session_start();
include 'config/db.php'; // ไฟล์เชื่อมต่อฐานข้อมูล


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // ตรวจสอบว่ามีอีเมลนี้อยู่ในระบบหรือไม่
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $username, $hashed_password);
        $stmt->fetch();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;

            header("Location: index.php"); // เข้าสู่ระบบสำเร็จ
            exit;
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง'); history.back();</script>";
        }
    } else {
        echo "<script>alert('ไม่พบผู้ใช้งานนี้'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();
    
}

?>
<?php if (isset($_SESSION["username"])): ?>
  <span class="navbar-text">สวัสดี, <?= $_SESSION["username"] ?></span>
<?php endif; ?>
