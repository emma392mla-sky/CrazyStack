<?php
// DB Connection Settings
$host = "sql200.infinityfree.com";
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha";
$charset = "utf8mb4";

// Set DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES     => false,
];

// Get POST data from JSON
$inputData = json_decode(file_get_contents("php://input"), true);

// Check if data is received correctly
if ($inputData) {
    // Extract the data from the request
    $phone = $inputData['user'];
    $amount = $inputData['deposit'];
    $bank = $inputData['bank'];
    $txId = $inputData['tx_id'];
    $time = $inputData['time'];
    $date = $inputData['date'];

    try {
        // Create a PDO instance
        $pdo = new PDO($dsn, $user, $pass, $options);

        // SQL to insert the deposit record
        $sql = "INSERT INTO deposits (phone, amount, bank, Trans_Id, time, date) 
                VALUES (:phone, :amount, :bank, :txId, :time, :date)";
        
        // Prepare statement
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':bank', $bank);
        $stmt->bindParam(':txId', $txId);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':date', $date);

        // Execute the query
        if ($stmt->execute()) {
            // Send success response
            echo json_encode(['success' => true]);
        } else {
            // Send error response if query failed
            echo json_encode(['success' => false, 'message' => 'Failed to save deposit record']);
        }
    } catch (PDOException $e) {
        // Catch any DB connection errors and return error message
        echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
    }
} else {
    // Send error response if no data received
    echo json_encode(['success' => false, 'message' => 'Invalid or missing data']);
}
?>
