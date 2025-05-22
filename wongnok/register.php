<?php
session_start();
include 'wongnok/config/db.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามาจาก POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // ตรวจสอบรหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านไม่ตรงกัน'); history.back();</script>";
        exit;
    }

    // ตรวจสอบว่าอีเมลนี้เคยลงทะเบียนไว้แล้วหรือไม่
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('อีเมลนี้ได้ลงทะเบียนแล้ว'); history.back();</script>";
        exit;
    }
    $stmt->close();

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // บันทึกข้อมูลลงฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        // สมัครเสร็จ → เข้าสู่ระบบอัตโนมัติ
        $_SESSION["user_id"] = $stmt->insert_id;
        $_SESSION["username"] = $username;
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลงทะเบียน'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>