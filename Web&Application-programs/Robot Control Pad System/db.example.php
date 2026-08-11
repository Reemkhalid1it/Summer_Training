<?php
$host   = "YOUR_HOST";
$user   = "YOUR_USERNAME";
$pass   = "YOUR_PASSWORD";
$dbname = "YOUR_DATABASE_NAME";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>