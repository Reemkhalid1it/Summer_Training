<?php
header('Content-Type: application/json');
require 'db.php';

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(["success" => false, "message" => "id غير صحيح"]);
    exit;
}

$stmt = $conn->prepare("UPDATE stu SET status = 1 - status WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // نرجع القيمة الجديدة عشان نحدثها بالواجهة
    $stmt2 = $conn->prepare("SELECT status FROM stu WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->bind_result($newStatus);
    $stmt2->fetch();
    $stmt2->close();

    echo json_encode(["success" => true, "status" => (int)$newStatus]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
