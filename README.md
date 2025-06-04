ลิงค์วีดีโอการติดตั้ง 
https://drive.google.com/file/d/1t6Hh-Kd1TsXn0PvdnyPRu6CMwB7_wTYf/view?usp=drive_link
เปิด MAMP Pro เพื่อ Start Server แล้วเปิด MAMP เพื่อ Start Server
เข้าที่ไฟล์ Indexhome.php เพื่อเริ่มต้นใช้งาน Web Wongnok
หากเปิดไม่ได้เป็นที่ไม่ได้ปิด - เปิด MAMP ใหม่อีกครับ
หากเปิดไม่ได้เป็นที่ไม่ได้สร้างตารางเก็บข้อมูลใน phpMyAdmin ให้ใส่ โค้ดในช่อง SQL ตามนี้

-- ตารางผู้ใช้
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตารางสูตรอาหาร
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    difficulty ENUM('ง่าย', 'ปานกลาง', 'ยาก') NOT NULL,
    cook_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ตารางการให้คะแนน
CREATE TABLE ratings (
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    PRIMARY KEY (user_id, recipe_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);
