<?php
// config/db.php
// Database connection using PDO

$host = 'srv1668.hstgr.io'; // Updated host
$db   = 'u280759006_biofuels'; // Updated database name
$user = 'u280759006_biofuels'; // Updated database username
$pass = 'Biofuels@99'; // Updated database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database connection failed: ' . $e->getMessage();
    exit;
}
