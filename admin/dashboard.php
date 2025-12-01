<?php
require_once __DIR__ . '/../config/config.php';

$adminModel = new Admin();

if (!$adminModel->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$reservationModel = new Reservation();
$tableModel = new Table();

$stats = $reservationModel->getStats();
$upcomingReservations = $reservationModel->getUpcoming(5);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
                <p>Přehled rezervačního systému</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Celkový počet rezervací</p>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['today']; ?></h3>
                        <p>Rezervace dnes</p>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Čekající na potvrzení</p>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['month']; ?></h3>
                        <p>Tento měsíc</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Reservations -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Nadcházející rezervace</h2>
                    <a href="reservations.php" class="btn btn-sm btn-primary">Zobrazit vše</a>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingReservations)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>Žádné nadcházející rezervace</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Datum</th>
                                        <th>Čas</th>
                                        <th>Zákazník</th>
                                        <th>Stůl</th>
                                        <th>Hosté</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingReservations as $reservation): ?>
                                        <tr>
                                            <td><?php echo date('d.m.Y', strtotime($reservation['reservation_date'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($reservation['reservation_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['table_number']); ?></td>
                                            <td><?php echo $reservation['guest_count']; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $reservation['status']; ?>">
                                                    <?php
                                                    $statusLabels = [
                                                        'pending' => 'Čeká',
                                                        'confirmed' => 'Potvrzeno',
                                                        'cancelled' => 'Zrušeno',
                                                        'completed' => 'Dokončeno'
                                                    ];
                                                    echo $statusLabels[$reservation['status']] ?? $reservation['status'];
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
