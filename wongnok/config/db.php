<?php
$host = "localhost";
$dbname = "wongnog_db"; // เปลี่ยนให้ตรงกับชื่อฐานข้อมูลของคุณ
$user = "root";         // ถ้าใช้ XAMPP ให้ใช้ root
$pass = "root";             // ถ้าใช้ XAMPP ปกติจะไม่มีรหัสผ่าน

$conn = new mysqli($host, $user, $pass, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
?>