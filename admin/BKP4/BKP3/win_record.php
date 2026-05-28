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

if (!$data || !isset($data['bet_user']) || !isset($data['won'])) {
    return_json(false, "Invalid input. Missing required fields.");
}

$phone_number = trim($data['bet_user']);
$stake = isset($data['stake']) ? round((float)$data['stake'], 2) : null;
$won = isset($data['won']) ? round((float)$data['won'], 2) : null;
$target = isset($data['target_landed']) ? $data['target_landed'] : null;
$time = isset($data['time']) ? $data['time'] : null;
$date = isset($data['date']) ? $data['date'] : null;

// Validate fields
if (empty($phone_number) || $stake === null || $won === null) {
    return_json(false, "Missing required win data.");
}

// --- DB OPERATIONS ---
try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Start a transaction to ensure both operations succeed or fail together
    $pdo->beginTransaction();

    // Insert the win record
    $stmt_insert = $pdo->prepare("INSERT INTO win (`phone`, `stake`, `won`, `target`, `time`, `date`) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->execute([$phone_number, $stake, $won, $target, $time, $date]);

    // Check if the insert was successful
    if ($stmt_insert->rowCount() === 0) {
        $pdo->rollBack();
        return_json(false, "Failed to insert win record.");
    }

    // Commit the transaction
    $pdo->commit();

    // Return success response
    return_json(true, "Win record successfully updated.", [
        'phone' => $phone_number,
        'stake' => $stake,
        'won' => $won,
        'target' => $target,
        'time' => $time,
        'date' => $date
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error for debugging
    error_log("Database error: " . $e->getMessage());

    // Return error response
    return_json(false, "Database error: " . $e->getMessage());
}
?>
