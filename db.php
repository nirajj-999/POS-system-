<?php
// Create a connection to the database
$conn = new mysqli("localhost", "root", "", "orderflow");

// Check connection
if (!$conn) {
    echo "Connection failed: " . mysqli_connect_error();
}
?>
