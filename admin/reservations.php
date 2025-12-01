<?php
require_once __DIR__ . '/../config/config.php';

$adminModel = new Admin();
if (!$adminModel->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$reservationModel = new Reservation();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        if ($reservationModel->update($_POST['id'], ['status' => $_POST['status']])) {
            $message = 'Status rezervace byl aktualizován.';
        } else {
            $error = 'Chyba při aktualizaci statusu.';
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        if ($reservationModel->delete($_POST['id'])) {
            $message = 'Rezervace byla smazána.';
        } else {
            $error = 'Chyba při mazání rezervace.';
        }
    }
}

// Get filters
$filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['date'])) {
    $filters['date'] = $_GET['date'];
}

$reservations = $reservationModel->getAll($filters);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervace - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-calendar-alt"></i> Správa rezervací</h1>
                <a href="reservation_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nová rezervace
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="">Všechny</option>
                                    <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Čeká</option>
                                    <option value="confirmed" <?php echo ($filters['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Potvrzeno</option>
                                    <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Zrušeno</option>
                                    <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Dokončeno</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Datum</label>
                                <input type="date" name="date" value="<?php echo htmlspecialchars($filters['date'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrovat
                                </button>
                                <a href="reservations.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Zrušit
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reservations Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($reservations)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>Žádné rezervace</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Datum</th>
                                        <th>Čas</th>
                                        <th>Zákazník</th>
                                        <th>Kontakt</th>
                                        <th>Stůl</th>
                                        <th>Hosté</th>
                                        <th>Status</th>
                                        <th>Akce</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <tr>
                                            <td><?php echo $reservation['id']; ?></td>
                                            <td><?php echo date('d.m.Y', strtotime($reservation['reservation_date'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($reservation['reservation_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($reservation['customer_email']); ?><br>
                                                    <?php echo htmlspecialchars($reservation['customer_phone']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo htmlspecialchars($reservation['table_number']); ?></td>
                                            <td><?php echo $reservation['guest_count']; ?></td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()" class="status-select">
                                                        <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>Čeká</option>
                                                        <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Potvrzeno</option>
                                                        <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Zrušeno</option>
                                                        <option value="completed" <?php echo $reservation['status'] === 'completed' ? 'selected' : ''; ?>>Dokončeno</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="actions">
                                                <a href="reservation_edit.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-primary" title="Upravit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Opravdu smazat tuto rezervaci?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Smazat">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>
