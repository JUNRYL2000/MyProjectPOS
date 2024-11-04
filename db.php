<?php
// Database connection parameters
$servername = "localhost"; // Database host
$username = "root"; // Replace with your database username
$password = "root"; // Replace with your database password
$dbname = "myprojectpos"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
