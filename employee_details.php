<?php
require_once 'includes/header.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($employee_id <= 0) {
    header('Location: admin_dashboard.php');
    exit();
}

// Database connection
    $conn = new mysqli('localhost', 'root', '', 'attendance_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Ensure MySQL session timezone is Philippines (GMT+8)
    $conn->query("SET time_zone = '+08:00'");

// Fetch employee details
$sql = "SELECT * FROM users WHERE id = ? AND role != 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    $_SESSION['error'] = 'Employee not found';
    header('Location: admin_dashboard.php');
    exit();
}

// Fetch attendance summary
$sql = "SELECT 
            COUNT(*) as total_days,
            SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, check_in, COALESCE(check_out, NOW())))) as total_hours
        FROM attendance 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch recent attendance
$sql = "SELECT * FROM attendance 
        WHERE user_id = ? 
        ORDER BY check_in DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$recent_attendance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch notes
$sql = "SELECT * FROM employee_notes 
        WHERE user_id = ? 
        ORDER BY note_date DESC, created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<div class="container">
    <div class="page-header">
        <div>
            <h2>Employee Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($employee['name']); ?></li>
                </ol>
            </nav>
        </div>
        <div class="header-actions">
            <a href="edit_employee.php?id=<?php echo $employee_id; ?>" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit
            </a>
            <a href="add_note.php?user_id=<?php echo $employee_id; ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Note
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card profile-card">
                <div class="card-body text-center">
                    <div class="avatar">
                        <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($employee['name']); ?></h3>
                    <p class="text-muted"><?php echo ucfirst($employee['role']); ?></p>
                    
                    <div class="employee-info">
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($employee['email']); ?></p>
                        <?php if (!empty($employee['phone'])): ?>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($employee['phone']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($employee['department'])): ?>
                            <p><i class="fas fa-building"></i> <?php echo htmlspecialchars($employee['department']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($employee['position'])): ?>
                            <p><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($employee['position']); ?></p>
                        <?php endif; ?>
                        <p><i class="fas fa-calendar-alt"></i> Joined on <?php echo date('M j, Y', strtotime($employee['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4>Attendance Summary</h4>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $summary['total_days'] ?? 0; ?></div>
                            <div class="stat-label">Total Days</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo substr($summary['total_hours'] ?? '00:00:00', 0, 5); ?>h</div>
                            <div class="stat-label">Total Hours</div>
                        </div>
                    </div>
                    <a href="view_attendance.php?user_id=<?php echo $employee_id; ?>" class="btn btn-outline btn-block mt-3">
                        View Full Attendance
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" type="button" role="tab">
                                Recent Activity
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
                                Notes
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="employeeTabsContent">
                        <!-- Recent Activity Tab -->
                        <div class="tab-pane fade show active" id="recent" role="tabpanel">
                            <?php if (empty($recent_attendance)): ?>
                                <div class="alert alert-info">No recent attendance records found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Check In</th>
                                                <th>Check Out</th>
                                                <th>Hours</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_attendance as $record): 
                                                $check_in = new DateTime($record['check_in']);
                                                $check_out = !empty($record['check_out']) ? new DateTime($record['check_out']) : null;
                                                $status = $check_out ? 'Completed' : 'Checked In';
                                                
                                                if ($check_out) {
                                                    $interval = $check_in->diff($check_out);
                                                    $hours = $interval->format('%h:%I');
                                                } else {
                                                    $now = new DateTime();
                                                    $interval = $check_in->diff($now);
                                                    $hours = $interval->format('%h:%I');
                                                }
                                            ?>
                                                <tr>
                                                    <td><?php echo $check_in->format('M j, Y'); ?></td>
                                                    <td><?php echo $check_in->format('g:i A'); ?></td>
                                                    <td><?php echo $check_out ? $check_out->format('g:i A') : '--'; ?></td>
                                                    <td><?php echo $hours; ?>h</td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $status === 'Completed' ? 'success' : 'warning'; ?>">
                                                            <?php echo $status; ?>
                                                        </span>
                                                        <?php if ($record['is_manual']): ?>
                                                            <span class="badge bg-info">Manual</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <?php if (empty($notes)): ?>
                                <div class="alert alert-info">No notes found for this employee.</div>
                            <?php else: ?>
                                <div class="notes-list">
                                    <?php foreach ($notes as $note): ?>
                                        <div class="note-item">
                                            <div class="note-header">
                                                <div class="note-date">
                                                    <?php echo date('M j, Y', strtotime($note['note_date'])); ?>
                                                    <span class="text-muted">• <?php echo date('g:i A', strtotime($note['created_at'])); ?></span>
                                                </div>
                                                <div class="note-actions">
                                                    <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="btn btn-sm btn-outline">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_note.php?id=<?php echo $note['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this note?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="note-content">
                                                <?php echo nl2br(htmlspecialchars($note['note'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Employee Details Styles */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin: 5px 0 0;
    font-size: 14px;
}

.breadcrumb-item a {
    color: var(--ford-blue);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.avatar {
    width: 100px;
    height: 100px;
    background: var(--ford-blue);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    font-weight: 600;
    margin: 0 auto 20px;
}

.employee-info {
    text-align: left;
    margin-top: 20px;
}

.employee-info p {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.employee-info i {
    width: 20px;
    text-align: center;
    color: #6c757d;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.stat-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: var(--ford-blue);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: #6c757d;
}

.nav-tabs {
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 0;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 12px 20px;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: var(--ford-blue);
    background: none;
    border-bottom: 2px solid var(--ford-blue);
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    text-align: left;
}

.table th {
    font-weight: 600;
    color: #555;
    background-color: #f9f9f9;
}

.badge {
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.notes-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.note-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 3px solid var(--ford-blue);
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.note-date {
    font-size: 13px;
    color: #6c757d;
}

.note-actions {
    display: flex;
    gap: 5px;
}

.note-content {
    line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .header-actions .btn {
        flex: 1;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>