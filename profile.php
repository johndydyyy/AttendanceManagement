<?php
require_once 'includes/header.php';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'attendance_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Ensure MySQL session timezone is Philippines (GMT+8)
$conn->query("SET time_zone = '+08:00'");

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email is already taken by another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $error = 'Email is already in use by another account';
        } else {
            // Handle password change if requested
            $password_update = '';
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error = 'All password fields are required to change password';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New password and confirm password do not match';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password must be at least 6 characters long';
                } else {
                    // Verify current password
                    if (password_verify($current_password, $user['password'])) {
                        $password_update = ", password = ?";
                    } else {
                        $error = 'Current password is incorrect';
                    }
                }
            }
            
            if (empty($error)) {
                // Update user data
                if (!empty($password_update)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET name = ?, email = ?{$password_update} WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
                } else {
                    $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $name, $email, $user_id);
                }
                
                if ($stmt->execute()) {
                    // Update session data
                    $_SESSION['username'] = $name;
                    $success = 'Profile updated successfully!';
                    // Refresh user data
                    $sql = "SELECT * FROM users WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                } else {
                    $error = 'Error updating profile: ' . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<div class="container">
    <div class="page-header">
        <h2>My Profile</h2>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card profile-card">
                <div class="card-body text-center">
                    <div class="avatar">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <h3 class="mt-3"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                    <div class="user-details">
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <?php if (!empty($user['phone'])): ?>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($user['department'])): ?>
                            <p><i class="fas fa-building"></i> <?php echo htmlspecialchars($user['department']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($user['position'])): ?>
                            <p><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($user['position']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Edit Profile</h3>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <hr class="my-4">
                        <h5>Change Password</h5>
                        <p class="text-muted">Leave these fields blank to keep your current password.</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <small class="form-text text-muted">At least 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Page Styles */
.page-header {
    margin-bottom: 25px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px;
}

.col-md-4, .col-md-8 {
    padding: 0 15px;
    margin-bottom: 30px;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    height: 100%;
    border: 1px solid #eee;
}

.card-body {
    padding: 25px;
}

.profile-card .card-body {
    padding: 30px 20px;
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

.user-details {
    text-align: left;
    margin-top: 20px;
}

.user-details p {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-details i {
    width: 20px;
    text-align: center;
    color: #6c757d;
}

.card-title {
    margin-bottom: 25px;
    font-size: 20px;
    color: #333;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 15px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    border-color: var(--ford-blue);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 52, 120, 0.1);
}

.alert {
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-size: 14px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.form-text {
    font-size: 12px;
    margin-top: 5px;
    color: #6c757d;
}

.form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    text-align: right;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background-color: var(--ford-blue);
    color: white;
}

.btn-primary:hover {
    background-color: var(--ford-light-blue);
}

hr {
    margin: 25px 0;
    border: 0;
    border-top: 1px solid #eee;
}

/* Responsive */
@media (max-width: 768px) {
    .row {
        flex-direction: column;
    }
    
    .col-md-4, .col-md-8 {
        width: 100%;
        max-width: 100%;
        padding: 0;
    }
    
    .profile-card {
        margin-bottom: 20px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
