<?php
require_once __DIR__ . '/../config/config.php';

$tableModel = new Table();
$reservationModel = new Reservation();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reserve') {
    $data = [
        'customer_name' => $_POST['customer_name'] ?? '',
        'customer_email' => $_POST['customer_email'] ?? '',
        'customer_phone' => $_POST['customer_phone'] ?? '',
        'guest_count' => $_POST['guest_count'] ?? 0,
        'reservation_date' => $_POST['reservation_date'] ?? '',
        'reservation_time' => $_POST['reservation_time'] ?? '',
        'special_requests' => $_POST['special_requests'] ?? '',
    ];

    // Find available table
    $availableTables = $tableModel->getAvailable($data['reservation_date'], $data['reservation_time'], $data['guest_count']);

    if (!empty($availableTables)) {
        $data['table_id'] = $availableTables[0]['id'];
        $data['status'] = 'pending';

        if ($reservationModel->create($data)) {
            $message = 'Rezervace byla úspěšně vytvořena! Brzy vás budeme kontaktovat pro potvrzení.';
        } else {
            $error = 'Chyba při vytváření rezervace. Zkuste to prosím znovu.';
        }
    } else {
        $error = 'Omlouváme se, pro zadaný čas a počet osob nemáme dostupný stůl.';
    }
}

$allTables = $tableModel->getAll();
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo RESTAURANT_NAME; ?> - Rezervační systém</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-utensils"></i>
                <span><?php echo RESTAURANT_NAME; ?></span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home">Domů</a></li>
                <li><a href="#reservation">Rezervace</a></li>
                <li><a href="#tables">Stoly</a></li>
                <li><a href="#contact">Kontakt</a></li>
                <li><a href="../admin" class="admin-link"><i class="fas fa-lock"></i> Admin</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Vítejte v <?php echo RESTAURANT_NAME; ?></h1>
            <p>Nejlepší gastronomický zážitek ve městě</p>
            <a href="#reservation" class="btn btn-primary">Rezervovat stůl</a>
        </div>
    </section>

    <!-- Reservation Section -->
    <section id="reservation" class="section">
        <div class="container">
            <h2 class="section-title">Rezervace stolu</h2>

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

            <div class="reservation-form-wrapper">
                <form method="POST" class="reservation-form">
                    <input type="hidden" name="action" value="reserve">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name">
                                <i class="fas fa-user"></i> Jméno a příjmení
                            </label>
                            <input type="text" id="customer_name" name="customer_name" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_email">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" id="customer_email" name="customer_email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_phone">
                                <i class="fas fa-phone"></i> Telefon
                            </label>
                            <input type="tel" id="customer_phone" name="customer_phone" required>
                        </div>

                        <div class="form-group">
                            <label for="guest_count">
                                <i class="fas fa-users"></i> Počet hostů
                            </label>
                            <select id="guest_count" name="guest_count" required>
                                <option value="">Vyberte...</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?>
                                        <?php echo $i === 1 ? 'osoba' : ($i <= 4 ? 'osoby' : 'osob'); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="reservation_date">
                                <i class="fas fa-calendar"></i> Datum
                            </label>
                            <input type="date" id="reservation_date" name="reservation_date"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="reservation_time">
                                <i class="fas fa-clock"></i> Čas
                            </label>
                            <select id="reservation_time" name="reservation_time" required>
                                <option value="">Vyberte...</option>
                                <?php
                                $start = strtotime(OPENING_TIME);
                                $end = strtotime(CLOSING_TIME);
                                for ($time = $start; $time <= $end; $time += 1800) {
                                    $timeStr = date('H:i', $time);
                                    echo "<option value='$timeStr'>$timeStr</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">
                            <i class="fas fa-comment"></i> Speciální požadavky
                        </label>
                        <textarea id="special_requests" name="special_requests" rows="3"
                            placeholder="Alergie, preference stolu, oslava..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-check"></i> Rezervovat
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Tables Section -->
    <section id="tables" class="section section-gray">
        <div class="container">
            <h2 class="section-title">Naše stoly</h2>
            <div class="tables-grid">
                <?php foreach ($allTables as $table): ?>
                    <div class="table-card">
                        <div class="table-icon">
                            <i class="fas fa-chair"></i>
                        </div>
                        <h3>Stůl <?php echo htmlspecialchars($table['table_number']); ?></h3>
                        <div class="table-info">
                            <p><i class="fas fa-users"></i> Kapacita: <?php echo $table['capacity']; ?> osob</p>
                            <p><i class="fas fa-map-marker-alt"></i> Umístění:
                                <?php echo htmlspecialchars($table['location']); ?></p>
                        </div>
                        <span class="table-status status-<?php echo $table['status']; ?>">
                            <?php
                            $statusText = [
                                'available' => 'Dostupný',
                                'reserved' => 'Rezervovaný',
                                'occupied' => 'Obsazený'
                            ];
                            echo $statusText[$table['status']] ?? $table['status'];
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <h2 class="section-title">Kontakt</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <h3>Telefon</h3>
                    <p><?php echo RESTAURANT_PHONE; ?></p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p><?php echo RESTAURANT_EMAIL; ?></p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <h3>Otevírací doba</h3>
                    <p><?php echo OPENING_TIME; ?> - <?php echo CLOSING_TIME; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo RESTAURANT_NAME; ?>. Všechna práva vyhrazena.</p>
            <p>Rezervační systém - Semestrální projekt</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>

</html>