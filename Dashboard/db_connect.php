<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = ""; // default XAMPP password is empty
$dbname = "silidcheck_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// You can now use $conn in your PHP files to interact with the database.
?>
