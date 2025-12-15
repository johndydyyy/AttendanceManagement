<?php
// Database configuration
$host = 'localhost';
$dbname = 'attendance_2';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Ensure MySQL session timezone is Philippines (GMT+8)
    try {
        $pdo->exec("SET time_zone = '+08:00'");
    } catch (PDOException $e) {
        // Non-fatal: log and continue
        error_log('db_connect.php: could not set time_zone: ' . $e->getMessage());
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
