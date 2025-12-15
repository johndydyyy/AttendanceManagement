<?php
require_once 'config/db_connect.php';

try {
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS attendance_system");
    $pdo->exec("USE attendance_system");

    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'employee') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Attendance table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            date DATE NOT NULL,
            time_in TIME,
            time_out TIME,
            status ENUM('present', 'absent', 'late') DEFAULT 'absent',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_attendance (user_id, date)
        )
    ");

    // Reports table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            admin_id INT NOT NULL,
            note TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    // Insert default admin (username: admin, password: admin123)
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, full_name, role) VALUES (?, ?, 'System Admin', 'admin')");
    $stmt->execute(['admin', $hashed_password]);

    echo "Database setup completed successfully!";
} catch (PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
}
?>
