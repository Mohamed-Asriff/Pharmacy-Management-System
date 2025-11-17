
<header class="main-header">
    <div class="header-content">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-title">
                <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <p><?php echo $page_description ?? 'Pharmacy Management System'; ?></p>
            </div>
        </div>
        
        <div class="header-right">
            <div class="datetime-display">
                <div class="greeting" id="greeting"></div>
                <div class="datetime" id="currentDateTime"></div>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-menu-trigger">
                    <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <i class="fas fa-user-circle"></i>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
                <div class="user-dropdown-menu">
                    <div class="user-info-dropdown">
                        <div class="user-avatar-sm">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-details-dropdown">
                            <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                            <span><?php echo ucfirst($_SESSION['user_role']); ?></span>
                            <small><?php echo htmlspecialchars($_SESSION['user_email']); ?></small>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="user-management.php" class="dropdown-item">
                        <i class="fas fa-users-cog"></i> User Management
                    </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="../logout.php" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Add this JavaScript immediately after the header -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dropdown script loaded');
    
    const dropdown = document.getElementById('userDropdown');
    const trigger = document.querySelector('.user-menu-trigger');
    
    if (dropdown && trigger) {
        console.log('Dropdown elements found');
        
        // Click event for dropdown trigger
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Dropdown clicked - toggling');
            dropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Close dropdown when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('active');
            }
        });
        
        // Close dropdown when clicking on dropdown items
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                dropdown.classList.remove('active');
            });
        });
        
    } else {
        console.error('Dropdown elements not found');
    }
});

// Basic datetime functionality
function updateDateTime() {
    const now = new Date();
    const greeting = document.getElementById('greeting');
    const datetime = document.getElementById('currentDateTime');
    
    if (greeting) {
        const hour = now.getHours();
        if (hour < 12) greeting.textContent = 'Good Morning';
        else if (hour < 18) greeting.textContent = 'Good Afternoon';
        else greeting.textContent = 'Good Evening';
    }
    
    if (datetime) {
        datetime.textContent = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
}

// Initialize datetime and update every second
updateDateTime();
setInterval(updateDateTime, 1000);
</script>
