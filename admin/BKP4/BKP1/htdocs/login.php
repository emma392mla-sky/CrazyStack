<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Set the default timezone for PHP operations to Central Africa Time (Malawi)
date_default_timezone_set('Africa/Blantyre');
// 2. Capture the current date and time in the specific format: Time | Date (e.g., 04:29:42 | 2025-11-20)
$current_time = date('H:i:s | Y-m-d'); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$is_options = ($_SERVER['REQUEST_METHOD'] === 'OPTIONS');
if (!$is_options) {
    header('Content-Type: application/json');
}

$response = ['success' => false];

// Database connection details (assuming these are correct)
$host = "sql200.infinityfree.com";
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha";
$charset = "utf8mb4";

if ($is_options) {
    http_response_code(200);
    exit;
}

$phone = $_POST['phone'] ?? null;
$pass_input = $_POST['pass'] ?? null;

if (!$phone || !$pass_input) {
    $input = json_decode(file_get_contents("php://input"), true);
    $phone = $input['phone'] ?? null;
    $pass_input = $input['pass'] ?? null;
}

// --- START: Added Length Validation ---
if (!$phone || !$pass_input) {
    echo json_encode(['success' => false, 'message' => 'Missing phone or password']);
    exit;
}

if (strlen($phone) !== 10) {
    echo json_encode(['success' => false, 'message' => 'Phone number must be exactly 10 digits']);
    exit;
}

if (strlen($pass_input) !== 4) {
    echo json_encode(['success' => false, 'message' => 'Password must be exactly 4 digits']);
    exit;
}
// --- END: Added Length Validation ---


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
        // User exists (Attempting Login)
        if ($user_data['pass'] === $pass_input) {
            
            // --- UPDATED: LAST LOGIN with "Time | Date" Format ---
            $update_stmt = $pdo->prepare("UPDATE users SET last_login = ? WHERE id = ?");
            // Use the $current_time variable which now holds the specific format
            $update_stmt->execute([$current_time, $user_data['id']]);
            // --- END UPDATE ---

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
        // User does not exist (Attempting Signup)
        
        // --- UPDATED: SIGNUP with "Time | Date" Format ---
        // Insert the $current_time for the initial last_login
        $stmt = $pdo->prepare("INSERT INTO users (phone, pass, balance, last_login) VALUES (?, ?, ?, ?)");
        $stmt->execute([$phone, $pass_input, 0, $current_time]);
        $new_user_id = $pdo->lastInsertId();
        // --- END UPDATE ---

        $response = [
            'success' => true,
            'message' => 'Signup successful',
            'user_id' => $new_user_id,
            'balance' => 0
        ];
    }

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>