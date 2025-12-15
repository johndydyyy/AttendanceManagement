<?php
require_once 'config/db_connect.php';
session_start();

// Set the default timezone to Philippines
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');
$current_datetime = date('Y-m-d H:i:s');
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['time_in', 'time_out'])) {
    try {
        if ($action === 'time_in') {
            // Check if user has already checked in today and not checked out
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(check_in) = ? AND check_out IS NULL");
            $stmt->execute([$user_id, $current_date]);
            $record = $stmt->fetch();

            if ($record) {
                $_SESSION['error'] = 'You have already checked in today and not checked out yet!';
            } else {
                // Create new record with current datetime for check_in
                $current_datetime = date('Y-m-d H:i:s');
                        $stmt = $pdo->prepare(
                            "INSERT INTO attendance (user_id, check_in) VALUES (?, ?)"
                        );
                $stmt->execute([$user_id, $current_datetime]);
                $_SESSION['message'] = 'Time in recorded successfully at ' . date('h:i A', strtotime($current_datetime));
            }
        } elseif ($action === 'time_out') {
            // Find the most recent check-in without a check-out
            $stmt = $pdo->prepare("
                SELECT * FROM attendance 
                WHERE user_id = ? 
                AND DATE(check_in) = ? 
                AND check_out IS NULL 
                ORDER BY check_in DESC 
                LIMIT 1
            ");
            $stmt->execute([$user_id, $current_date]);
            $record = $stmt->fetch();

            if ($record) {
                $current_datetime = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("
                    UPDATE attendance 
                    SET check_out = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$current_datetime, $record['id']]);
                $_SESSION['message'] = 'Time out recorded successfully at ' . date('h:i A', strtotime($current_datetime));
            } else {
                $_SESSION['error'] = 'No active check-in found. Please check in first!';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'employee_dashboard.php'));
exit();
?>