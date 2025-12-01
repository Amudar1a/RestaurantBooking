<?php
require_once __DIR__ . '/../config/config.php';

$adminModel = new Admin();
if (!$adminModel->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$reservationModel = new Reservation();
$tableModel = new Table();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: reservations.php');
    exit;
}

$reservation = $reservationModel->getById($id);
if (!$reservation) {
    header('Location: reservations.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'customer_name' => $_POST['customer_name'] ?? '',
        'customer_email' => $_POST['customer_email'] ?? '',
        'customer_phone' => $_POST['customer_phone'] ?? '',
        'guest_count' => $_POST['guest_count'] ?? 0,
        'reservation_date' => $_POST['reservation_date'] ?? '',
        'reservation_time' => $_POST['reservation_time'] ?? '',
        'special_requests' => $_POST['special_requests'] ?? '',
        'status' => $_POST['status'] ?? 'pending',
        'table_id' => $_POST['table_id'] ?? $reservation['table_id']
    ];

    if ($reservationModel->update($id, $data)) {
        $message = 'Rezervace byla úspěšně aktualizována.';
        $reservation = $reservationModel->getById($id);
    } else {
        $error = 'Chyba při aktualizaci rezervace.';
    }
}

$tables = $tableModel->getAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravit rezervaci - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Upravit rezervaci #<?php echo $id; ?></h1>
                <a href="reservations.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zpět
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

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_name">Jméno zákazníka</label>
                                <input type="text" id="customer_name" name="customer_name"
                                       value="<?php echo htmlspecialchars($reservation['customer_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="customer_email">Email</label>
                                <input type="email" id="customer_email" name="customer_email"
                                       value="<?php echo htmlspecialchars($reservation['customer_email']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_phone">Telefon</label>
                                <input type="tel" id="customer_phone" name="customer_phone"
                                       value="<?php echo htmlspecialchars($reservation['customer_phone']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="guest_count">Počet hostů</label>
                                <input type="number" id="guest_count" name="guest_count" min="1"
                                       value="<?php echo $reservation['guest_count']; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="reservation_date">Datum</label>
                                <input type="date" id="reservation_date" name="reservation_date"
                                       value="<?php echo $reservation['reservation_date']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="reservation_time">Čas</label>
                                <input type="time" id="reservation_time" name="reservation_time"
                                       value="<?php echo $reservation['reservation_time']; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="table_id">Stůl</label>
                                <select id="table_id" name="table_id" required>
                                    <?php foreach ($tables as $table): ?>
                                        <option value="<?php echo $table['id']; ?>"
                                                <?php echo $table['id'] == $reservation['table_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($table['table_number']); ?> -
                                            <?php echo $table['capacity']; ?> osob -
                                            <?php echo htmlspecialchars($table['location']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" required>
                                    <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>Čeká</option>
                                    <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Potvrzeno</option>
                                    <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Zrušeno</option>
                                    <option value="completed" <?php echo $reservation['status'] === 'completed' ? 'selected' : ''; ?>>Dokončeno</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="special_requests">Speciální požadavky</label>
                            <textarea id="special_requests" name="special_requests" rows="3"><?php echo htmlspecialchars($reservation['special_requests']); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-save"></i> Uložit změny
                        </button>
                    </form>
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
