<?php
require_once __DIR__ . '/../config/config.php';

$adminModel = new Admin();

// Check if already logged in
if ($adminModel->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $admin = $adminModel->authenticate($username, $password);

    if ($admin) {
        $adminModel->login($admin);
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Nesprávné přihlašovací údaje';
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo RESTAURANT_NAME; ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-lock"></i>
                <h2>Administrace</h2>
                <p><?php echo RESTAURANT_NAME; ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Uživatelské jméno
                    </label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i> Heslo
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-sign-in-alt"></i> Přihlásit se
                </button>
            </form>

            <div class="login-footer">
                <a href="../public/index.php">
                    <i class="fas fa-arrow-left"></i> Zpět na web
                </a>
            </div>

            <div class="login-hint">
                <small><i class="fas fa-info-circle"></i> Výchozí: admin / 12345</small>
            </div>
        </div>
    </div>
</body>
</html>
