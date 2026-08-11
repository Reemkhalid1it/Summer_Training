<?php
header('Content-Type: application/json');
require 'db.php';

$result = $conn->query("SELECT id, name, age, status FROM stu ORDER BY id DESC");

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
$conn->close();
?>
