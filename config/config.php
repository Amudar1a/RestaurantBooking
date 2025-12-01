<?php
session_start();

// Timezone
date_default_timezone_set('Europe/Prague');

// Base URL
define('BASE_URL', '/RestaurantBooking');

// Restaurant settings
define('RESTAURANT_NAME', 'La Bella Vista');
define('RESTAURANT_EMAIL', 'info@bellavista.cz');
define('RESTAURANT_PHONE', '+420 123 456 789');
define('OPENING_TIME', '11:00');
define('CLOSING_TIME', '23:00');

// Include database
require_once __DIR__ . '/database.php';

// Autoloader for classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../src/Models/',
        __DIR__ . '/../src/Controllers/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});
