<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
    header('Location: employee_dashboard.php');
    exit();
}

$user_id = $_GET['user_id'] ?? 0;

// Get employee details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$employee = $stmt->fetch();

if (!$employee) {
    $_SESSION['error'] = 'Employee not found';
    header('Location: admin_dashboard.php');
    exit();
}

// Get attendance records
$stmt = $pdo->prepare("
    SELECT * FROM attendance
    WHERE user_id = ?
    ORDER BY check_in DESC
    LIMIT 30
");
$stmt->execute([$user_id]);
$attendance = $stmt->fetchAll();
?>

<div class="dashboard-section">
    <h2>Attendance for <?php echo htmlspecialchars($employee['full_name']); ?></h2>
    
    <div class="back-link" style="margin-bottom: 20px;">
        <a href="admin_dashboard.php" class="btn btn-secondary">
            &laquo; Back to Dashboard
        </a>
    </div>
    
    
    
    <div class="attendance-records">
        <h3>Attendance History (Last 30 Days)</h3>
        <?php if (count($attendance) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Hours Worked</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_hours = 0;
                        $present_days = 0;
                        foreach ($attendance as $record): 
                            $time_in = isset($record['check_in']) ? strtotime($record['check_in']) : 0;
                            $time_out = !empty($record['check_out']) ? strtotime($record['check_out']) : 0;
                            $hours_worked = 0;
                            
                            if ($time_out > $time_in && $time_in > 0) {
                                $diff = $time_out - $time_in;
                                $hours_worked = round($diff / 3600, 2); // Convert seconds to hours
                                $total_hours += $hours_worked;
                                $present_days++;
                            }
                            
                            $status_class = '';
                            if ($record['status'] === 'present') {
                                $status_class = 'status-present';
                            } elseif ($record['status'] === 'late') {
                                $status_class = 'status-late';
                            } else {
                                $status_class = 'status-absent';
                            }
                        ?>
                        <tr>
                            <td><?php echo $time_in ? date('M d, Y', $time_in) : '-'; ?></td>
                            <td><?php echo $time_in ? date('h:i A', $time_in) : '-'; ?></td>
                            <td><?php echo $time_out ? date('h:i A', $time_out) : '-'; ?></td>
                            <td>
                                <?php
                                    // Determine status from check_in/check_out
                                    if ($time_in == 0) {
                                        $display_status = 'Absent';
                                    } elseif ($time_out == 0) {
                                        $display_status = 'Late';
                                    } else {
                                        $display_status = 'Present';
                                    }
                                ?>
                                <span class="status-badge <?php echo strtolower($display_status) == 'present' ? 'status-present' : (strtolower($display_status) == 'late' ? 'status-late' : 'status-absent'); ?>"><?php echo $display_status; ?></span>
                            </td>
                            <td><?php echo $hours_worked > 0 ? $hours_worked . ' hrs' : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: bold;">Total Present Days:</td>
                            <td><?php echo $present_days; ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: bold;">Total Hours Worked:</td>
                            <td><?php echo number_format($total_hours, 2); ?> hrs</td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: bold;">Average Hours/Day:</td>
                            <td><?php echo $present_days > 0 ? number_format($total_hours / $present_days, 2) : '0'; ?> hrs</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <p>No attendance records found.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.note-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    min-height: 100px;
    resize: vertical;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
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

tfoot tr:first-child td {
    border-top: 2px solid #dee2e6;
    padding-top: 10px;
}

tfoot tr:last-child td {
    font-weight: bold;
    font-size: 1.1em;
}

.table-responsive {
    overflow-x: auto;
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

td {
    vertical-align: middle;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .data-table {
        display: block;
    }
    
    .data-table thead {
        display: none;
    }
    
    .data-table tbody, .data-table tr, .data-table td {
        display: block;
        width: 100%;
    }
    
    .data-table tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
    }
    
    .data-table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    
    .data-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        text-align: left;
        font-weight: bold;
    }
}
</style>

