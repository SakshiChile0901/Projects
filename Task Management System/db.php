<?php
// db.php
$host = "127.0.0.1:3307";
$db   = "task_system";
$user = "root";
$pass = ""; // XAMPP default has no password

$dsn = "mysql:host=127.0.0.1:3307;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    // In production do not echo errors. For dev we show it so you can debug.
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed", "message" => $e->getMessage()]);
    exit;
}

// Start session for APIs that use authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
