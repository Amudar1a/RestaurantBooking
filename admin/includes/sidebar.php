<aside class="admin-sidebar" id="sidebar">
    <nav class="admin-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="reservations.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Rezervace</span>
        </a>
        <a href="tables.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'tables.php' ? 'active' : ''; ?>">
            <i class="fas fa-chair"></i>
            <span>Stoly</span>
        </a>
        <a href="../public/index.php" class="nav-item" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>Zobrazit web</span>
        </a>
    </nav>
</aside>
