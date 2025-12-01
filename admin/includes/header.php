<header class="admin-header">
    <div class="admin-header-left">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1><?php echo RESTAURANT_NAME; ?></h1>
    </div>
    <div class="admin-header-right">
        <span class="admin-user">
            <i class="fas fa-user-circle"></i>
            <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
        </span>
        <a href="logout.php" class="btn btn-sm btn-danger">
            <i class="fas fa-sign-out-alt"></i> Odhlásit
        </a>
    </div>
</header>
