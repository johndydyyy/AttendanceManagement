<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');
$current_month = date('n');
$current_year = date('Y');

// Get the first day of the month
$first_day = mktime(0, 0, 0, $current_month, 1, $current_year);
$month_name = date('F', $first_day);
$day_of_week = date('D', $first_day);
$days_in_month = cal_days_in_month(0, $current_month, $current_year);

// Get all attendance for current month
$stmt = $pdo->prepare("
    SELECT * FROM attendance
    WHERE user_id = ?
    AND MONTH(check_in) = ?
    AND YEAR(check_in) = ?
    ORDER BY check_in ASC
");
$stmt->execute([$user_id, $current_month, $current_year]);
$attendance_records = $stmt->fetchAll();

// Create array of days with attendance status
$attendance_days = [];
foreach ($attendance_records as $record) {
    $day = date('j', strtotime($record['check_in']));
    $status = !empty($record['check_out']) ? 'present' : 'late';
    $attendance_days[$day] = $status;
}

// Get current month's attendance for the table
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
<div class="dashboard-section">
    <h2>Attendance Calendar - <?php echo $month_name . ' ' . $current_year; ?></h2>
    <div class="calendar-container">
        <div class="calendar-header">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>
        <div class="calendar-grid">
            <?php
            // Add empty cells for days before the first day of the month
            $first_day_of_week = date('w', $first_day);
            for ($i = 0; $i < $first_day_of_week; $i++) {
                echo '<div class="calendar-day empty"></div>';
            }

            // Add days of the month
            for ($day = 1; $day <= $days_in_month; $day++) {
                $current_day = date('Y-m-d', mktime(0, 0, 0, $current_month, $day, $current_year));
                $is_today = ($current_day == $current_date) ? 'today' : '';
                $status = isset($attendance_days[$day]) ? 'status-' . $attendance_days[$day] : '';
                $status_text = isset($attendance_days[$day]) ? ucfirst($attendance_days[$day]) : '';
                
                echo "<div class='calendar-day $is_today $status' title='$status_text'>";
                echo "<span class='day-number'>$day</span>";
                if (isset($attendance_days[$day])) {
                    echo "<span class='status-indicator'></span>";
                }
                echo "</div>";
            }
            ?>
        </div>
        <div class="calendar-legend">
            <div class="legend-item"><span class="status-indicator status-present"></span> Present</div>
            <div class="legend-item"><span class="status-indicator status-late"></span> Late</div>
            <div class="legend-item"><span class="status-indicator status-absent"></span> Absent</div>
        </div>
    </div>
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

/* Calendar Styles */
.calendar-container {
    max-width: 800px;
    margin: 20px auto;
    font-family: Arial, sans-serif;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: #f8f9fa;
    font-weight: bold;
    text-align: center;
    padding: 10px 0;
    border-radius: 5px 5px 0 0;
    border: 1px solid #dee2e6;
    border-bottom: none;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    grid-gap: 1px;
    background-color: #e9ecef;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 5px 5px;
    overflow: hidden;
}

.calendar-day {
    min-height: 80px;
    padding: 5px;
    background-color: white;
    position: relative;
    transition: all 0.2s;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
}

.calendar-day:hover {
    background-color: #f8f9fa;
    transform: scale(1.02);
    z-index: 1;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.calendar-day.empty {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
}

.calendar-day.today {
    font-weight: bold;
    background-color: #e7f5ff;
    border: 2px solid #339af0;
}

.day-number {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 2px;
}

.status-indicator {
    display: block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin: 2px auto;
}

.status-present .status-indicator {
    background-color: #28a745;
}

.status-late .status-indicator {
    background-color: #ffc107;
}

.status-absent .status-indicator {
    background-color: #dc3545;
}

.calendar-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
    font-size: 14px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.legend-item .status-indicator {
    margin: 0;
    width: 12px;
    height: 12px;
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


