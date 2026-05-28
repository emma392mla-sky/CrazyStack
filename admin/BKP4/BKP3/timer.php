<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Set timezone
date_default_timezone_set('Africa/Blantyre');

// Read JSON
$input = json_decode(file_get_contents("php://input"), true);
$user = $input['user'] ?? null;   // phone or id

if (!$user) {
    echo json_encode(["success" => false, "message" => "No user provided"]);
    exit;
}

// Format time
$current_time = date('H:i:s | Y-m-d');

// DB connection
$host = "sql200.infinityfree.com";
$username = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha";
$charset = "utf8mb4";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=$charset",
        $username,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // UPDATE time only
    $stmt = $pdo->prepare("UPDATE users SET last_login = ? WHERE phone = ?");
    $stmt->execute([$current_time, $user]);

    echo json_encode([
        "success" => true,
        "time" => $current_time
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
