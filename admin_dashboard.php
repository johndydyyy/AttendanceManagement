<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
    header('Location: employee_dashboard.php');
    exit();
}

// Get all employees
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'employee' ORDER BY full_name");
$employees = $stmt->fetchAll();
?>

<h2>Admin Dashboard</h2>

<div class="dashboard-section">
    <h3>Employee List</h3>
    <?php if (count($employees) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?php echo $employee['id']; ?></td>
                    <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($employee['username']); ?></td>
                    <td class="actions">
                        <a href="view_attendance.php?user_id=<?php echo $employee['id']; ?>" class="btn btn-view">View</a>
                        <a href="delete_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-danger" style="background-color: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; text-decoration: none; display: inline-block;" onclick="return confirm('Are you sure you want to discharge this employee? This action cannot be undone.')">Discharge</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No employees found.</p>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h3>Add New Employee</h3>
    <form method="POST" action="add_employee.php" class="employee-form">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Employee</button>
    </form>
</div>

<style>
.dashboard-section {
    background: white;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.data-table th, .data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.data-table tr:hover {
    background-color: #f5f5f5;
}

.btn {
    display: inline-block;
    padding: 6px 12px;
    margin: 2px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-view {
    background-color: #17a2b8;
    color: white;
}

.btn-note {
    background-color: #ffc107;
    color: #212529;
}

.btn-primary {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
}

.employee-form {
    max-width: 500px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input[type="text"],
.form-group input[type="password"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}
</style>


