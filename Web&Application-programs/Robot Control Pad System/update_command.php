<?php
header('Content-Type: application/json');
include "db.php";

// Map the button name to the character that needs to be stored
$map = [
    "forward"  => "f",
    "backward" => "b",
    "left"     => "l",
    "right"    => "r",
    "stop"     => "S"
];

$button = isset($_POST['command']) ? $_POST['command'] : '';

if (!array_key_exists($button, $map)) {
    echo json_encode(["status" => "error", "message" => "أمر غير معروف"]);
    exit;
}

$letter = $map[$button];

try {
    // Update the only row (id = 1) instead of adding a new row
    $stmt = $conn->prepare("UPDATE robot_state SET command = ? WHERE id = 1");
    $stmt->bind_param("s", $letter);
    $stmt->execute();

    echo json_encode(["status" => "success", "button" => $button, "stored_as" => $letter]);

    $stmt->close();
    $conn->close();
} catch (mysqli_sql_exception $e) {
    echo json_encode(["status" => "error", "message" => "فشل تنفيذ الاستعلام: " . $e->getMessage()]);
}
?>
