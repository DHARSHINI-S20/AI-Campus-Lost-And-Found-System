<?php
// Central connection file — every other page requires this instead of reconnecting.
$host = "localhost";
$user = "root";
$pass = "";       // default XAMPP MySQL password is blank
$db   = "lost_found_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
?>
