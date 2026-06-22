<?php
/**
 * Hadero Coffee Database Connection
 * Configured for XAMPP (Apache/MySQL) local environment
 */

$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP empty password
$dbname = 'hadero_db';

try {
    // Connect using PDO with general UTF-8 encoding support
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch associative arrays by default
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // Fail gracefully with descriptive notes
    die("Database Connection failed! Please make sure MySQL is started in XAMPP and the 'hadero_db' schema has been loaded. Details: " . $e->getMessage());
}
?>
