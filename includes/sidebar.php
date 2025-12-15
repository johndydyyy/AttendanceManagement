<?php
// Get the current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
        </div>
        <span class="logo-text">Menu</span>
    </div>
    
    <ul class="sidebar-nav">
        <li class="nav-item <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>">
            <a href="admin_dashboard.php">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Employees and Attendance pages removed per request; use Admin Dashboard and View Attendance instead -->
        <?php endif; ?>
        
        <!-- My Profile link removed to use sidebar only (per request) -->
        
        <li class="nav-item">
            <a href="?logout=1">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    background: var(--ford-white);
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.sidebar-header {
    padding: 20px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #eee;
    height: 60px;
    background: var(--ford-blue);
    color: white;
}

.logo-icon {
    margin-right: 12px;
    display: flex;
    align-items: center;
}

.logo-text {
    font-size: 18px;
    font-weight: 600;
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0;
}

.nav-item {
    margin: 5px 10px;
    border-radius: 6px;
    overflow: hidden;
}

.nav-item a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #555;
    text-decoration: none;
    transition: all 0.2s ease;
}

.nav-item:hover a {
    background: #f5f5f5;
    color: var(--ford-blue);
}

.nav-item.active a {
    background: rgba(0, 52, 120, 0.1);
    color: var(--ford-blue);
    font-weight: 500;
}

.nav-icon {
    margin-right: 12px;
    display: flex;
    align-items: center;
}

.nav-text {
    font-size: 14px;
}

.sidebar-footer {
    padding: 15px;
    border-top: 1px solid #eee;
}

.user-info {
    display: flex;
    align-items: center;
    padding: 10px 0;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--ford-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 12px;
}

.user-details {
    line-height: 1.3;
}

.user-name {
    font-weight: 500;
    font-size: 14px;
}

.user-role {
    font-size: 12px;
    color: #777;
}

/* Adjust main content when sidebar is present */
body {
    padding-left: 250px;
}

.header {
    left: 250px;
    width: calc(100% - 250px);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    body {
        padding-left: 0;
    }
    
    .header {
        left: 0;
        width: 100%;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        display: none;
    }
    
    .sidebar-overlay.active {
        display: block;
    }
}
</style>
