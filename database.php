<?php
// ==========================================
// Pet Adoption System
// Database Connection (Procedural MySQLi)
// ==========================================

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "pet_adoption_system";

// Create Connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check Connection
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Set character encoding
mysqli_set_charset($conn, "utf8");
?>