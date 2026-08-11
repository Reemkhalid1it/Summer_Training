<?php
header('Content-Type: application/json');
require 'db.php';

$name = trim($_POST['name'] ?? '');
$age  = trim($_POST['age'] ?? '');

if ($name === '' || $age === '' || !is_numeric($age)) {
    echo json_encode(["success" => false, "message" => "الرجاء إدخال اسم وعمر صحيحين"]);
    exit;
}

// Prepared statement instead of directly embedding user input in the query
$stmt = $conn->prepare("INSERT INTO stu (name, age, status) VALUES (?, ?, 0)");
$stmt->bind_param("si", $name, $age);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "id" => $stmt->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
