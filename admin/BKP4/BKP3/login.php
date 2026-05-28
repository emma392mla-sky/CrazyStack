<?php
// 1. Start output buffering to prevent accidental whitespace or warnings from breaking JSON
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone
date_default_timezone_set('Africa/Blantyre');
$current_time = date('H:i:s | Y-m-d'); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$is_options = ($_SERVER['REQUEST_METHOD'] === 'OPTIONS');
if (!$is_options) {
    header('Content-Type: application/json');
} else {
    http_response_code(200);
    exit;
}

$response = ['success' => false, 'message' => 'Unknown error occurred'];

// Database connection details
$host = "sql200.infinityfree.com";
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha";
$charset = "utf8mb4";

// Capture Input
$phone = $_POST['phone'] ?? null;
$pass_input = $_POST['pass'] ?? null;

if (!$phone || !$pass_input) {
    $input = json_decode(file_get_contents("php://input"), true);
    $phone = $input['phone'] ?? null;
    $pass_input = $input['pass'] ?? null;
}

// Validation
if (!$phone || !$pass_input) {
    $response['message'] = 'Missing phone or password';
} elseif (strlen($phone) !== 10) {
    $response['message'] = 'Phone number must be exactly 10 digits';
} elseif (strlen($pass_input) !== 4) {
    $response['message'] = 'Password must be exactly 4 digits';
} else {
    // Database Logic
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $stmt = $pdo->prepare("SELECT id, pass, balance FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user_data = $stmt->fetch();

        if ($user_data) {
            // Attempting Login
            if ($user_data['pass'] === $pass_input) {
                $update_stmt = $pdo->prepare("UPDATE users SET last_login = ? WHERE id = ?");
                $update_stmt->execute([$current_time, $user_data['id']]);

                $response = [
                    'success' => true,
                    'message' => 'Login successful',
                    'user_id' => $user_data['id'],
                    'balance' => $user_data['balance']
                ];
            } else {
                $response['message'] = 'Incorrect password';
            }
        } else {
            // Attempting Signup
            $stmt = $pdo->prepare("INSERT INTO users (phone, pass, balance, last_login) VALUES (?, ?, ?, ?)");
            $stmt->execute([$phone, $pass_input, 0, $current_time]);
            
            $response = [
                'success' => true,
                'message' => 'Signup successful',
                'user_id' => $pdo->lastInsertId(),
                'balance' => 0
            ];
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

// 2. Clear the buffer and send ONLY the JSON
$final_output = json_encode($response);
ob_end_clean(); 
echo $final_output;
exit;