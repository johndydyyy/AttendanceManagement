<?php
require_once 'config/db_connect.php';
session_start();

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Only administrators can add employees.';
    header('Location: employee_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }

    if (empty($errors)) {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                throw new Exception('Username already exists');
            }

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new employee
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, full_name, role) 
                VALUES (?, ?, ?, 'employee')
            ");
            $stmt->execute([$username, $hashed_password, $full_name]);
            
            $_SESSION['message'] = 'Employee added successfully!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error adding employee: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

header('Location: admin_dashboard.php');
exit();
?>