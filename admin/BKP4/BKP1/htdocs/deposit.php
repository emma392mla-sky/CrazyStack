<?php
/**
 * Deposit API Endpoint
 * Handles incoming JSON deposit request, updates user balance,
 * and logs the transaction details to deposit.log.
 */

header('Content-Type: application/json');
date_default_timezone_set('Africa/Blantyre'); // Ensure correct timezone for logging

// --- DB Connection Details ---
// NOTE: These credentials should ideally be stored outside the web root or in environment variables for production security.
$host = "sql200.infinityfree.com";
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha"; 
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES      => false,
];
// ------------------------------------------------

// --- Helper Functions ---

/**
 * Sends a JSON response and terminates the script.
 */
function return_json(bool $success, string $message = null, array $data = []): void {
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

/**
 * Logs a successful or failed deposit to deposit.log.
 */
function logDeposit(string $status, string $phone_number, float $amount, float $new_balance = 0.0, string $message = ''): void {
    $timestamp = date('j M Y, g:i A');
    
    // --- CHANGE: Use __DIR__ for a reliable, absolute path ---
    $logFile = __DIR__ . '/deposit.log'; 

    $logEntry = str_repeat('-', 60) . "\n";
    $logEntry .= "STATUS     : $status\n";
    $logEntry .= "Phone      : $phone_number\n";
    $logEntry .= "Amount MWK : " . number_format($amount, 2) . "\n";
    if ($new_balance > 0) {
        $logEntry .= "New Balance: " . number_format($new_balance, 2) . "\n";
    }
    $logEntry .= "Date/Time  : $timestamp\n";
    if ($message) {
        $logEntry .= "Message    : $message\n";
    }
    $logEntry .= str_repeat('-', 60) . "\n\n";

    $result = file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Debugging check for file write failure
    if ($result === false) {
        error_log("LOGGING FAILED: Could not write to $logFile for phone $phone_number. Check directory permissions.");
    }
}
// ------------------------

// --- Input Validation ---
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['bet_user']) || !isset($data['bet_amount'])) {
    return_json(false, "Invalid input. Phone number and amount are required.");
}

$phone_number = $data['bet_user'];
$deposit_amount = (float)($data['bet_amount'] ?? 0.0);
// $order_id is ignored

if ($deposit_amount <= 0 || !is_numeric($deposit_amount)) {
    logDeposit('Failed', $phone_number, $deposit_amount, 0.0, 'Invalid deposit amount.');
    return_json(false, "Invalid amount. The deposit amount must be a positive number.");
}
if (empty($phone_number)) {
    logDeposit('Failed', $phone_number, $deposit_amount, 0.0, 'Invalid phone number.');
    return_json(false, "Invalid phone number.");
}
// ------------------------

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->beginTransaction();  

    // 1. ATOMIC BALANCE UPDATE
    $stmt_update = $pdo->prepare("UPDATE users SET balance = ROUND(balance + ?, 2) WHERE phone = ?");
    $stmt_update->execute([$deposit_amount, $phone_number]);

    if ($stmt_update->rowCount() === 0) {
        $pdo->rollBack();
        logDeposit('Failed', $phone_number, $deposit_amount, 0.0, "User not found or balance not updated.");
        return_json(false, "Transaction failed. User not found (check 'users' table and 'phone' column) or balance not updated.");
    }
    
    // 2. Fetch the newly updated balance
    $stmt_select = $pdo->prepare("SELECT balance FROM users WHERE phone = ?");  
    $stmt_select->execute([$phone_number]);
    $user_data = $stmt_select->fetch();

    if (!$user_data) {
        $pdo->rollBack(); 
        logDeposit('Failed', $phone_number, $deposit_amount, 0.0, "Could not retrieve updated balance after update.");
        return_json(false, "Internal Error: Could not retrieve updated balance.");
    }

    $new_balance = (float)$user_data['balance'];

    // Commit the changes
    $pdo->commit();  

    // 3. SUCCESS LOGGING
    logDeposit('Success', $phone_number, $deposit_amount, $new_balance, 'Balance updated successfully.');
    
    // Return the new balance
    return_json(true, "Deposit processed successfully.", ['new_balance' => number_format($new_balance, 2, '.', '')]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log DB error for debugging
    $error_msg = $e->getMessage();
    error_log("Database Error in deposit.php: " . $error_msg);  
    logDeposit('DB_Error', $phone_number ?? 'N/A', $deposit_amount ?? 0.0, 0.0, 'Database error during transaction: ' . $error_msg);
    return_json(false, "DB Error: something went wrong");
}