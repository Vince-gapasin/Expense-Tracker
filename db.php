<?php
$host = 'localhost';
$db   = 'pennywise_db';
$user = 'root'; // Change if your username is different
$pass = '';     // Change if you have a password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>