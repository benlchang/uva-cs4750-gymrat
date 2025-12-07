<?php
$username = 'Master';
$password = 'Adnan123!';
$host = '127.0.0.1'; 
$port = '3307';      
$dbname = 'gymrat';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $db = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo "<p>Database connection error: " . $e->getMessage() . "</p>";
    exit();
}
?>
