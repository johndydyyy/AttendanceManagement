<?php
ob_start();
// Set default timezone to Philippines (GMT+8)
date_default_timezone_set('Asia/Manila');
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Manangement</title>
    <style>
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1100;
            background: var(--ford-blue);
            border: none;
            border-radius: 4px;
            color: white;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Header Styles */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
        }
        
        .header-right {
            display: flex;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .welcome-text {
            font-size: 14px;
        }
        
        .user-role-badge {
            background: rgba(255,255,255,0.2);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        :root {
            --ford-blue: #003478;
            --ford-light-blue: #2c6ab3;
            --ford-white: #ffffff;
            --ford-gray: #f5f5f5;
            --ford-dark: #222222;
        }
        
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; 
            padding: 0;
            background-color: var(--ford-gray);
            color: #333;
            line-height: 1.6;
        }
        
        .header { 
            background: var(--ford-blue); 
            color: var(--ford-white); 
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header a { 
            color: var(--ford-white); 
            text-decoration: none; 
            margin-left: 25px;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        .header a:hover {
            background-color: var(--ford-light-blue);
            transform: translateY(-2px);
        }
        
        .logo { 
            font-size: 24px; 
            font-weight: bold;
            display: flex;
            align-items: center;
            color: var(--ford-white);
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        .user-info { 
            display: flex; 
            align-items: center;
        }
        
        .user-info span { 
            margin-right: 20px;
            color: var(--ford-white);
            font-weight: 500;
        }
        
        .logout-btn {
            background-color: #e31837;
            padding: 8px 20px !important;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #c4122f;
            transform: translateY(-2px);
        }
        
        /* Delete button styles */
        .btn-delete {
            background-color: #e31837;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
        }
        
        .btn-delete:hover {
            background-color: #c4122f;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .main-content {
            min-height: 100vh;
            padding: 30px 0;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
    <script>
    // Toggle sidebar on mobile
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
        
        // Close sidebar when clicking on a nav item on mobile
        const navItems = document.querySelectorAll('.nav-item a');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });
        
        // Handle window resize
        function handleResize() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }
        
        window.addEventListener('resize', handleResize);
    });
    </script>
</head>
<body>
    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay"></div>
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" aria-label="Toggle menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>
    
    <div class="main-content">
        <div class="container">
            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
                unset($_SESSION['message']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>
