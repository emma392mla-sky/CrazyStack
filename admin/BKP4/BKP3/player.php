<?php
header('Content-Type: application/json');

// --- Updated Database Configuration ---
$host = "sql200.infinityfree.com";
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha"; // Cleaned: No non-ASCII or extra whitespace after $db
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

function return_json($success, $message = null, $data = []) {
    $response = ['success' => $success];
    if ($message) {
        $response['message'] = $message;
    }
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['log_player']) || empty($data['log_player'])) {
    return_json(false, "Invalid input. The phone number ('log_player') is missing or empty.");
}

$phone_number = $data['log_player'];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Updated query to use table 'users' and column 'phone'
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE phone = ? LIMIT 1");
    
    $stmt->execute([$phone_number]);
    
    $row = $stmt->fetch();

    if ($row) {
        return_json(true, "Balance retrieved.", ['balance' => $row['balance']]);
    } else {
        return_json(false, "User not found. No account linked to this phone number.");
    }

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage()); 
    return_json(false, "Database error: Could not process request.");
}

?>
