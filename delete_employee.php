<?php
session_start();
require_once 'config/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid employee ID';
    header('Location: admin_dashboard.php');
    exit();
}

$employee_id = (int)$_GET['id'];

try {
    // Begin transaction
    $pdo->beginTransaction();

    // First, delete related records (if any) - adjust these based on your database schema
    // Example: $pdo->prepare("DELETE FROM attendance WHERE user_id = ?")->execute([$employee_id]);
    // Example: $pdo->prepare("DELETE FROM notes WHERE user_id = ?")->execute([$employee_id]);

    // Then delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'");
    $stmt->execute([$employee_id]);

    // Check if any row was affected
    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        $_SESSION['success'] = 'Employee account deleted successfully';
    } else {
        throw new Exception('No employee found with that ID or deletion failed');
    }
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error deleting employee: ' . $e->getMessage();
}

// Redirect back to admin dashboard
header('Location: admin_dashboard.php');
exit();
?>
