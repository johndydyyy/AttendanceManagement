<?php
// Attendance page removed — redirect to admin dashboard
header('Location: admin_dashboard.php');
exit();
    // Fallback to a safe, escaped query to avoid fatal errors
    $escaped_date = $conn->real_escape_string($date);
    $fallback_sql = "SELECT a.*, u.name as employee_name \
        FROM attendance a \
        JOIN users u ON a.user_id = u.id \
        WHERE DATE(a.check_in) = '" . $escaped_date . "' \
        ORDER BY a.check_in DESC";
    $result = $conn->query($fallback_sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
    }
}

// Fetch all employees for manual check-in/out
$sql = "SELECT id, name FROM users WHERE role != 'admin' ORDER BY name ASC";
$employees = $conn->query($sql);
if ($employees === false) {
    error_log('attendance.php: employees query failed: ' . $conn->error);
    <?php
    // Attendance page removed per request. Redirect to admin dashboard.
    header('Location: admin_dashboard.php');
    exit();
    ?>

    <div class="container">
                                    <td><?php echo $check_out ? $check_out->format('M j, Y h:i A') : '--'; ?></td>
                                    <td><?php echo $hours; ?></td>
                                    <td>
                                        <span class="badge <?php echo $status == 'Completed' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <?php if (!$check_out): ?>
                                            <a href="#" class="btn btn-sm btn-primary check-out" data-id="<?php echo $record['id']; ?>">
                                                Check Out
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Manual Entry Modal -->
<div id="manualEntryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Manual Attendance Entry</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="manualEntryForm">
                <div class="form-group">
                    <label for="employee">Employee</label>
                    <select id="employee" name="employee" class="form-control" required>
                        <option value="">Select Employee</option>
                        <?php while ($employee = $employees->fetch_assoc()): ?>
                            <option value="<?php echo $employee['id']; ?>"><?php echo htmlspecialchars($employee['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="checkInDate">Check-in Date</label>
                        <input type="datetime-local" id="checkInDate" name="check_in_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="checkOutDate">Check-out Date (Optional)</label>
                        <input type="datetime-local" id="checkOutDate" name="check_out_date" class="form-control">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="cancelManualEntry">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.date-navigation {
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-input {
    width: 160px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

/* Card */
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.card-body {
    padding: 25px;
}

/* Table */
.table-responsive {
    overflow-x: auto;
}

table.data-table {
    width: 100%;
    border-collapse: collapse;
}

table.data-table th,
table.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

table.data-table th {
    font-weight: 600;
    color: #555;
    background-color: #f9f9f9;
}

table.data-table tr:hover {
    background-color: #f5f9ff;
}

.actions {
    white-space: nowrap;
    text-align: right;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 15px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    gap: 5px;
}

.btn svg {
    width: 16px;
    height: 16px;
}

.btn-primary {
    background-color: var(--ford-blue);
    color: white;
}

.btn-primary:hover {
    background-color: var(--ford-light-blue);
}

.btn-outline {
    background: transparent;
    border: 1px solid #ddd;
    color: #555;
}

.btn-outline:hover {
    background: #f5f5f5;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 13px;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.close {
    font-size: 24px;
    font-weight: bold;
    color: #777;
    cursor: pointer;
}

.close:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
}

/* Form */
.form-group {
    margin-bottom: 15px;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-row .form-group {
    flex: 1;
    margin-bottom: 0;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #555;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    border-color: var(--ford-blue);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 52, 120, 0.1);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .date-navigation {
        width: 100%;
        margin-top: 10px;
    }
    
    .date-input {
        flex: 1;
    }
    
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
}
</style>

<script>
// Date Picker
const datePicker = document.getElementById('datePicker');
datePicker.addEventListener('change', function() {
    window.location.href = '?date=' + this.value;
});

// Manual Entry Modal
const modal = document.getElementById('manualEntryModal');
const btn = document.getElementById('manualEntryBtn');
const span = document.getElementsByClassName('close')[0];
const cancelBtn = document.getElementById('cancelManualEntry');

btn.onclick = function() {
    modal.style.display = 'flex';
    // Set default check-in time to current time
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000; // Convert to milliseconds
    const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
    document.getElementById('checkInDate').value = localISOTime;
    document.getElementById('checkOutDate').value = '';
}

span.onclick = function() {
    modal.style.display = 'none';
}

cancelBtn.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Handle form submission
document.getElementById('manualEntryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    
    // Send AJAX request
    fetch('save_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Attendance recorded successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save attendance'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Handle check-out
const checkOutButtons = document.querySelectorAll('.check-out');
checkOutButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const attendanceId = this.getAttribute('data-id');
        
        if (confirm('Are you sure you want to check out this employee?')) {
            const formData = new FormData();
            formData.append('action', 'check_out');
            formData.append('attendance_id', attendanceId);
            
            fetch('save_attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update check-out time'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
