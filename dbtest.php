<?php
// Load your database connection file
require('connect-db.php');

// If no error was thrown, connection succeeded
echo "<h2>Database connection successful!</h2>";

try {
    // Test query
    $result = $db->query("SELECT NOW() as current_time");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "<p>MySQL server time: " . $row['current_time'] . "</p>";
} 
catch (Exception $e) {
    echo "<p>Query test failed: " . $e->getMessage() . "</p>";
}
?>
