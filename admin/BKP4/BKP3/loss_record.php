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

// Check for missing required fields
$required_fields = ['bet_user', 'bet_amount', 'target_landed', 'target_selected', 'time', 'date'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        return_json(false, "Missing required field: " . $field);
    }
}

// Extract values from the data array
$phone_number = trim($data['bet_user']);
$bet_amount = round((float)$data['stake'], 2); // Round to 2 decimal places
$target_landed = $data['target_landed'];
$target_selected = $data['target_selected'];
$time = $data['time'];
$date = $data['date'];

// Validate phone number
if (empty($phone_number)) {
    return_json(false, "Invalid user identifier (phone number missing).");
}

// Validate bet amount
if ($bet_amount <= 0) {
    return_json(false, "Transaction amount must be positive.");
}

// --- DB OPERATIONS ---
try {
    // Connect to the database
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Start a transaction to ensure both operations succeed or fail together
    $pdo->beginTransaction();

    // 1. Insert the loss record into the `lose` table (no 'stake' field anymore)
    $stmt_insert = $pdo->prepare("INSERT INTO lose (`phone`, `amount_lost`, `target_landed`, `target_selected`, `time`, `date`) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->execute([$phone_number, $bet_amount, $target_landed, $target_selected, $time, $date]);

    // Check if the insert was successful
    if ($stmt_insert->rowCount() === 0) {
        $pdo->rollBack();
        return_json(false, "Failed to insert loss record.");
    }

    // Commit the transaction
    $pdo->commit();

    // Return a success response
    return_json(true, "Loss record successfully updated.", [
        'phone' => $phone_number,
        'bet_amount' => $bet_amount,
        'target_landed' => $target_landed,
        'target_selected' => $target_selected,
        'time' => $time,
        'date' => $date
    ]);

} catch (PDOException $e) {
    // Rollback the transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error for debugging
    error_log("Database error: " . $e->getMessage());

    // Return a detailed error response
    return_json(false, "Database error: " . $e->getMessage());
}
?>
