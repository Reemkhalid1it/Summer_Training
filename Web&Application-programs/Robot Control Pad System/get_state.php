<?php
header('Content-Type: application/json');
include "db.php";

try {
    $result = $conn->query("SELECT command, updated_at FROM robot_state WHERE id = 1");
    $row = $result->fetch_assoc();

    echo json_encode(["status" => "success", "data" => $row]);

    $conn->close();
} catch (mysqli_sql_exception $e) {
    echo json_encode(["status" => "error", "message" => "فشل جلب البيانات: " . $e->getMessage()]);
}
?>
