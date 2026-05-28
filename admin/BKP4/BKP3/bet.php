<?php
header('Content-Type: application/json');

// --- DB CONNECTION SETTINGS ---
$host = "sql200.infinityfree.com";
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES     => false,
];

// --- HELPER FUNCTION TO RETURN JSON ---
function return_json($success, $message = null, $data = []) {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if (!empty($data)) $response = array_merge($response, $data);
    echo json_encode($response);
    exit;
}

// --- INPUT VALIDATION ---
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['bet_user']) || !isset($data['bet_amount'])) {
    return_json(false, "Invalid input. Missing required fields.");
}

$phone_number = trim($data['bet_user']);
$bet_amount = round((float)$data['bet_amount'], 2); // Round to 2 decimal places
$mode = isset($data['bet_mode']) ? strtolower(trim($data['bet_mode'])) : 'deposit'; // Default to 'deposit'

$stake = isset($data['stake']) ? round((float)$data['stake'], 2) : null;
$won = isset($data['won']) ? round((float)$data['won'], 2) : null;
$target = isset($data['target']) ? $data['target'] : null;
$time = isset($data['time']) ? $data['time'] : null;
$date = isset($data['date']) ? $data['date'] : null;

// Validate phone number
if (empty($phone_number)) {
    return_json(false, "Invalid user identifier (phone number missing).");
}

// Validate bet amount
if ($bet_amount <= 0) {
    return_json(false, "Transaction amount must be positive.");
}

// Validate mode
if (!in_array($mode, ['deposit', 'withdraw'])) {
    return_json(false, "Invalid mode. Must be 'deposit' or 'withdraw'.");
}

// --- DB OPERATIONS ---
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Start a transaction to ensure both operations succeed or fail together
    $pdo->beginTransaction();

    // 1. Fetch the user's current balance and lock the row to prevent race conditions
    $stmt_select = $pdo->prepare("SELECT balance FROM users WHERE phone = ? FOR UPDATE");
    $stmt_select->execute([$phone_number]);
    $user_data = $stmt_select->fetch();

    if (!$user_data) {
        $pdo->rollBack();
        return_json(false, "User not found.");
    }

    $current_balance = (float)$user_data['balance'];
    $new_balance = $current_balance; // Initialize
    $modifier = 0;

    // 2. Handle the bet transaction (deposit or withdrawal)
    if ($mode === 'withdraw') {
        if ($current_balance < $bet_amount) {
            $pdo->rollBack();
            return_json(false, "Insufficient funds for withdrawal. Current balance: MWK " . number_format($current_balance, 2));
        }
        $modifier = -$bet_amount;
        $new_balance = round($current_balance - $bet_amount, 2);
    } else { // deposit
        $modifier = $bet_amount;
        $new_balance = round($current_balance + $bet_amount, 2);
    }

    // Update the user's balance
    $stmt_update = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE phone = ?");
    $stmt_update->execute([$modifier, $phone_number]);

    // Check if the update was successful
    if ($stmt_update->rowCount() === 0) {
        $pdo->rollBack();
        return_json(false, "Failed to update balance.");
    }

    // Commit the transaction
    $pdo->commit();

    return_json(true, ucfirst($mode) . " successful.", [
        'new_balance' => number_format($new_balance, 2, '.', '') // Return the updated balance
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack(); // Rollback the transaction on error
    }
    // Log the error for debugging
    error_log("Database error: " . $e->getMessage());
    return_json(false, "A server error occurred during the transaction. Please try again.");
}
?>
