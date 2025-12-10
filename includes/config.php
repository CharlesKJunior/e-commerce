<?php
// includes/config.php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'phpmyadmin');
define('DB_PASS', '55011224Mc?');
define('DB_NAME', 'roncom_ecommerce');

// Site Configuration
define('SITE_NAME', 'Roncom Networking Store');
define('SITE_URL', 'http://localhost/roncom'); // Change this for production
define('SITE_EMAIL', 'sales@roncom.com');
define('SITE_PHONE', '+256 772 123 456');

// Payment Configuration
define('CURRENCY', 'UGX');
define('TAX_RATE', 0.18); // 18% VAT for Uganda

// MTN Mobile Money Config
define('MTN_API_KEY', 'your_mtn_api_key');
define('MTN_API_SECRET', 'your_mtn_api_secret');
define('MTN_ENVIRONMENT', 'sandbox'); // sandbox or production

// Airtel Money Config
define('AIRTEL_API_KEY', 'your_airtel_api_key');
define('AIRTEL_API_SECRET', 'your_airtel_api_secret');
define('AIRTEL_ENVIRONMENT', 'sandbox');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_password');
define('SMTP_FROM', 'noreply@roncom.com');

// Timezone (Uganda)
date_default_timezone_set('Africa/Kampala');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}
?>