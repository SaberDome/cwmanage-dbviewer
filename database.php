<?php
session_start();

// Prefer environment variables, fall back to session
$serverName = getenv('DB_HOST') ?: ($_SESSION['hostname'] ?? null);
$database = getenv('DB_NAME') ?: ($_SESSION['dbname'] ?? null);
$username = getenv('DB_USER') ?: ($_SESSION['username'] ?? null);
$password = getenv('DB_PASS') ?: ($_SESSION['password'] ?? null);
$ignoreTrust = getenv('DB_IGNORE_TRUST') ?: ($_SESSION['ignore_trust'] ?? 'on');

if (!$serverName || !$database || !$username || !$password) {
    $dbErrorMessage = "No database connection details available.";
    header("Location: /index.php?message=" . urlencode($dbErrorMessage));
    exit;
}

$isIgnoreTrust = filter_var($ignoreTrust, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';

$isDbConnected = false;

try {
    $encodedPassword = addcslashes($password, '{}');
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    $conn = new PDO("sqlsrv:server=$serverName;Database=$database;Encrypt=true;TrustServerCertificate=$isIgnoreTrust", $username, $encodedPassword, $options);
    $isDbConnected = true;
} catch(PDOException $e) {
    error_log($e->getMessage());
    $dbErrorMessage = $e->getMessage();
    header("Location: /index.php?message=" . urlencode($dbErrorMessage));
    exit;
}
