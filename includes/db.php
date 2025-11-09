<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die('Environment file not found. Please copy .env.example to .env and configure it.');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Database configuration from environment variables
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$db   = $_ENV['DB_NAME'] ?? 'code_canvas';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    \PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new \PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Beginner-friendly error message
    $error_msg = "❌ <strong>Database Connection Failed</strong><br><br>";
    
    if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
        $error_msg .= "<strong>Error:</strong> " . $e->getMessage() . "<br><br>";
    }
    
    $error_msg .= "<strong>Common Solutions:</strong><br>";
    $error_msg .= "1. ✓ Check if MySQL is running in XAMPP Control Panel<br>";
    $error_msg .= "2. ✓ Verify database name in .env is: <code>code_canvas</code><br>";
    $error_msg .= "3. ✓ Make sure you imported create_tables.sql in phpMyAdmin<br>";
    $error_msg .= "4. ✓ If you set a MySQL password, add it to .env after DB_PASS=<br><br>";
    $error_msg .= "<strong>Need help?</strong> Check the Troubleshooting section in README.md";
    
    die("<div style='font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; border: 2px solid #e74c3c; background: #fadbd8; border-radius: 8px;'>" . $error_msg . "</div>");
}
?>