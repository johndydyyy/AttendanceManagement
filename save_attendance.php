<?php
session_start();
require_once 'config/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $conn = new mysqli('localhost', 'root', '', 'attendance_db');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    // Ensure MySQL session timezone is Philippines (GMT+8)
    $conn->query("SET time_zone = '+08:00'");

    $action = $_POST['action'] ?? '';

    if ($action === 'check_in') {
        // Handle check-in
        $user_id = $_SESSION['user_id'];
        $check_in = date('Y-m-d H:i:s');
        $notes = $_POST['notes'] ?? '';

        // Check if already checked in today
        $sql = "SELECT id FROM attendance WHERE user_id = ? AND DATE(check_in) = CURDATE() AND check_out IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['message'] = 'You have already checked in today';
        } else {
            $sql = "INSERT INTO attendance (user_id, check_in, notes) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user_id, $check_in, $notes);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Successfully checked in at ' . date('g:i A', strtotime($check_in));
            } else {
                throw new Exception("Error checking in: " . $stmt->error);
            }
        }
    } 
    elseif ($action === 'check_out') {
        // Handle check-out
        $attendance_id = (int)$_POST['attendance_id'] ?? 0;
        $check_out = date('Y-m-d H:i:s');
        
        // Verify ownership for non-admin users
        if ($_SESSION['role'] !== 'admin') {
            $sql = "SELECT id FROM attendance WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $attendance_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Invalid attendance record");
            }
        }

        $sql = "UPDATE attendance SET check_out = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $check_out, $attendance_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Successfully checked out at ' . date('g:i A', strtotime($check_out));
        } else {
            throw new Exception("Error checking out: " . $stmt->error);
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'manual_entry') {
        // Handle manual entry (admin only)
        if ($_SESSION['role'] !== 'admin') {
            throw new Exception("Unauthorized access");
        }

        $user_id = (int)$_POST['user_id'];
        $check_in = $_POST['check_in_date'];
        $check_out = !empty($_POST['check_out_date']) ? $_POST['check_out_date'] : null;
        $notes = $_POST['notes'] ?? '';

        $sql = "INSERT INTO attendance (user_id, check_in, check_out, notes, is_manual) VALUES (?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $check_in, $check_out, $notes);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Attendance record added successfully';
        } else {
            throw new Exception("Error saving attendance: " . $stmt->error);
        }
    }
    else {
        $response['message'] = 'Invalid action';
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    if (!isset($response['code'])) {
        $response['code'] = 500;
    }
}

echo json_encode($response);