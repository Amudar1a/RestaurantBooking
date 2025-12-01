<?php
require_once __DIR__ . '/../config/config.php';

$adminModel = new Admin();
if (!$adminModel->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$tableModel = new Table();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'table_number' => $_POST['table_number'] ?? '',
            'capacity' => $_POST['capacity'] ?? 0,
            'location' => $_POST['location'] ?? '',
            'status' => $_POST['status'] ?? 'available'
        ];

        if ($tableModel->create($data)) {
            $message = 'Stůl byl úspěšně přidán.';
        } else {
            $error = 'Chyba při přidávání stolu.';
        }
    } elseif ($action === 'update' && isset($_POST['id'])) {
        $data = [
            'table_number' => $_POST['table_number'] ?? '',
            'capacity' => $_POST['capacity'] ?? 0,
            'location' => $_POST['location'] ?? '',
            'status' => $_POST['status'] ?? 'available'
        ];

        if ($tableModel->update($_POST['id'], $data)) {
            $message = 'Stůl byl úspěšně aktualizován.';
        } else {
            $error = 'Chyba při aktualizaci stolu.';
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        if ($tableModel->delete($_POST['id'])) {
            $message = 'Stůl byl smazán.';
        } else {
            $error = 'Chyba při mazání stolu. Možná má aktivní rezervace.';
        }
    }
}

$tables = $tableModel->getAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stoly - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-chair"></i> Správa stolů</h1>
                <button onclick="showAddModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nový stůl
                </button>
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
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Číslo stolu</th>
                                    <th>Kapacita</th>
                                    <th>Umístění</th>
                                    <th>Status</th>
                                    <th>Akce</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables as $table): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($table['table_number']); ?></strong></td>
                                        <td><?php echo $table['capacity']; ?> osob</td>
                                        <td><?php echo htmlspecialchars($table['location']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $table['status']; ?>">
                                                <?php
                                                $statusText = [
                                                    'available' => 'Dostupný',
                                                    'reserved' => 'Rezervovaný',
                                                    'occupied' => 'Obsazený'
                                                ];
                                                echo $statusText[$table['status']] ?? $table['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button onclick='editTable(<?php echo json_encode($table); ?>)' class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Opravdu smazat tento stůl?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $table['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="tableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nový stůl</h2>
                <button onclick="closeModal()" class="modal-close">&times;</button>
            </div>
            <form method="POST" id="tableForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="tableId">

                <div class="form-group">
                    <label for="table_number">Číslo stolu</label>
                    <input type="text" id="table_number" name="table_number" required>
                </div>

                <div class="form-group">
                    <label for="capacity">Kapacita</label>
                    <input type="number" id="capacity" name="capacity" min="1" required>
                </div>

                <div class="form-group">
                    <label for="location">Umístění</label>
                    <input type="text" id="location" name="location">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="available">Dostupný</option>
                        <option value="reserved">Rezervovaný</option>
                        <option value="occupied">Obsazený</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Uložit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Nový stůl';
            document.getElementById('formAction').value = 'create';
            document.getElementById('tableForm').reset();
            document.getElementById('tableId').value = '';
            document.getElementById('tableModal').style.display = 'flex';
        }

        function editTable(table) {
            document.getElementById('modalTitle').textContent = 'Upravit stůl';
            document.getElementById('formAction').value = 'update';
            document.getElementById('tableId').value = table.id;
            document.getElementById('table_number').value = table.table_number;
            document.getElementById('capacity').value = table.capacity;
            document.getElementById('location').value = table.location;
            document.getElementById('status').value = table.status;
            document.getElementById('tableModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('tableModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('tableModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
