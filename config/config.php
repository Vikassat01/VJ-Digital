<?php
// Site Configuration
define('SITE_NAME', 'VJ Digital');
define('SITE_URL', 'http://localhost/VJ Digital/');
define('ADMIN_EMAIL', 'admin@vjdigital.com');

// Currency Settings
define('CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');

// File Upload Settings
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/VJ Digital/uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB

// Session Configuration
session_start();

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
