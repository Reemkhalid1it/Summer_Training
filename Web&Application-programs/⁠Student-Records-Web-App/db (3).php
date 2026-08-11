<?php
// إعدادات الاتصال بقاعدة البيانات (نفس بياناتك من infinityfree)
$servername = "sql308.infinityfree.com";
$username   = "if0_42361786";
$password   = "EPub8Ul06DW";
$dbname     = "if0_42361786_myfrist";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");
?>
