<?php
session_start();

// Set JSON response header
header('Content-Type: application/json');

// --- DATABASE CONFIGURATION ---
$host = "sql200.infinityfree.com"; 
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db = "if0_40320527_kwacha";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$admin_user = $input['user'] ?? '';
$admin_pass = $input['key'] ?? '';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo json_encode(['status' => 'success', 'message' => 'Already logged in']);
    exit;
}

// Validate input
if (empty($admin_user) || empty($admin_pass)) {
    echo json_encode(['status' => 'error', 'message' => 'User and password required']);
    exit;
}

// Check credentials
$stmt = $pdo->prepare("SELECT * FROM admin WHERE user = :user LIMIT 1");
$stmt->execute(['user' => $admin_user]);
$admin = $stmt->fetch();

if ($admin && password_verify($admin_pass, $admin['password'])) {
    // Correct password: create session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_user'] = $admin['user'];

    // Optional: update last login time
    $stmt = $pdo->prepare("UPDATE admin SET date_time = NOW() WHERE id = :id");
    $stmt->execute(['id' => $admin['id']]);

    echo json_encode(['status' => 'success', 'message' => 'Login successful']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
}
?>
