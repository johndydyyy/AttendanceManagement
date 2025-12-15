<?php
session_start();
require_once 'config/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Hardcoded admin login
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        header('Location: admin_dashboard.php');
        exit();
    }
    
    // Employee login
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'employee'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = 'employee';
        header('Location: employee_dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 400px; 
            margin: 50px auto; 
            padding: 20px;
            background-color: #f5f5f5;
        }
        .login-form { 
            background: white;
            border: 1px solid #ddd; 
            padding: 30px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button { 
            background: #4CAF50; 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .error { 
            color: #d32f2f; 
            margin-bottom: 20px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
            text-align: center;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Attendance Management System</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
