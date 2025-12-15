<?php
require_once __DIR__ . '/config/db_connect.php';

// Create default admin user with username 'admin' and password 'admin123'
$username = 'admin';
$password_plain = 'admin123';
$full_name = 'System Administrator';

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo "Admin user already exists.\n";
        exit;
    }

    $hashed = password_hash($password_plain, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
    $stmt->execute([$username, $hashed, $full_name]);

    echo "Admin user created. Username: admin, Password: admin123\n";
} catch (Exception $e) {
    echo "Error creating admin: " . $e->getMessage();
}

?>