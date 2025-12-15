<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

// Get current month's attendance
    $stmt = $pdo->prepare("
        SELECT * FROM attendance
        WHERE user_id = ?
        AND DATE(check_in) BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01') AND LAST_DAY(NOW())
        ORDER BY check_in DESC
    ");
$stmt->execute([$user_id]);
$attendance = $stmt->fetchAll();

// Get employee notes
$stmt = $pdo->prepare("
    SELECT r.*, u.username as admin_name 
    FROM reports r
    JOIN users u ON r.admin_id = u.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();
?>

<div class="dashboard-section">
    <h2>My Attendance</h2>
    
    <div class="attendance-actions">
        <form method="POST" action="record_attendance.php" class="inline-form">
            <button type="submit" name="action" value="time_in" class="btn btn-success">
                <i class="fas fa-sign-in-alt"></i> Time In
            </button>
        </form>
        <form method="POST" action="record_attendance.php" class="inline-form">
            <button type="submit" name="action" value="time_out" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Time Out
            </button>
        </form>
    </div>
</div>

<div class="dashboard-section">
    <h3>This Month's Attendance</h3>
    <?php if (count($attendance) > 0): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): 
                        $checkIn = isset($record['check_in']) ? new DateTime($record['check_in']) : null;
                        $checkOut = !empty($record['check_out']) ? new DateTime($record['check_out']) : null;
                        $displayDate = $checkIn ? $checkIn->format('M d, Y') : '-';
                        $timeIn = $checkIn ? $checkIn->format('h:i A') : '-';
                        $timeOut = $checkOut ? $checkOut->format('h:i A') : '-';
                        $statusKey = $checkOut ? 'present' : 'late';
                    ?>
                    <tr>
                        <td><?php echo $displayDate; ?></td>
                        <td><?php echo $timeIn; ?></td>
                        <td><?php echo $timeOut; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $statusKey; ?>">
                                <?php echo ucfirst($statusKey); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No attendance records found for this month.</p>
    <?php endif; ?>
</div>


<style>
.dashboard-section {
    background: white;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.attendance-actions {
    display: flex;
    gap: 15px;
    margin: 20px 0;
}

.inline-form {
    display: inline-block;
    margin: 0;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn i {
    font-size: 14px;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
}

.data-table th, .data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-present {
    background-color: #d4edda;
    color: #155724;
}

.status-absent {
    background-color: #f8d7da;
    color: #721c24;
}

.status-late {
    background-color: #fff3cd;
    color: #856404;
}

.notes-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.note-card {
    border: 1px solid #eee;
    border-radius: 6px;
    padding: 15px;
    background-color: #f9f9f9;
}

.note-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 14px;
    color: #666;
}

.note-content {
    line-height: 1.5;
    color: #333;
}

.table-responsive {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .attendance-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .note-header {
        flex-direction: column;
        gap: 5px;
    }
}
</style>


