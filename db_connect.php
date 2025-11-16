<?php
// DB connection for local XAMPP (adjust user/password if needed)
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default
$DB_NAME = 'zututors';
$charset = 'utf8mb4';

// Connect directly to the existing database (no CREATE statements)
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // For local development it's OK to show the error; adjust for production
    die('DB Connection failed: ' . $e->getMessage());
}
