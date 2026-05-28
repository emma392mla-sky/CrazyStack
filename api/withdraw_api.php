<?php
// API/withdraw_api.php
// Secure Backend - Handles Secrets & Transaction Logic

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// 1. CONFIGURATION (SECRETS LIVE HERE)
 $PAYCHANGU_SECRET_KEY = 'sec-live-Xvad0XHGxKVQ0w3QpIDIpNh1POaHtTkS';
 $PAYCHANGU_BASE_URL = 'https://api.paychangu.com/mobile-money/payouts/initialize';
 $SUPABASE_URL = 'https://awnzbiatwnfmryerfxwg.supabase.co';
 $SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg';

// Operator IDs
 $OPERATORS = [
    'airtel' => '20be6c20-adeb-4b5b-a7ba-0769820df4fb',
    'tnm'    => '27494cb5-ba9e-437f-a114-4e7a7686bcca'
];

// 2. GET INPUT
 $input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

 $phone = $input['phone'] ?? '';
 $amount = floatval($input['amount'] ?? 0);
 $sessionPhone = $input['sessionPhone'] ?? '';

// 3. VALIDATE INPUT
if (empty($phone) || empty($sessionPhone)) {
    echo json_encode(['success' => false, 'error' => 'Phone number missing']);
    exit;
}

if ($amount < 50) {
    echo json_encode(['success' => false, 'error' => 'Minimum withdrawal is MWK 50']);
    exit;
}

// Normalize Phone (10 digit format)
 $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($cleanPhone) == 9) $cleanPhone = '0' . $cleanPhone;

// Detect Network
 $network = 'airtel'; // default
if (preg_match('/^(088|087|081)/', $cleanPhone)) $network = 'tnm';

// 4. CHECK BALANCE IN SUPABASE
 $checkUrl = $SUPABASE_URL . "/rest/v1/users?phone=eq." . $sessionPhone . "&select=balance";
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $checkUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $SUPABASE_KEY",
    "Authorization: Bearer $SUPABASE_KEY"
]);
 $response = curl_exec($ch);
curl_close($ch);

 $userData = json_decode($response, true);

if (empty($userData) || !isset($userData[0]['balance'])) {
    echo json_encode(['success' => false, 'error' => 'User not found in database']);
    exit;
}

 $currentBalance = floatval($userData[0]['balance']);

if ($currentBalance < $amount) {
    echo json_encode([
        'success' => false, 
        'error' => "Insufficient funds! Available: MWK $currentBalance"
    ]);
    exit;
}

// 5. CALL PAYCHANGU API (SECURE)
 $paychanguPhone = ltrim($cleanPhone, '0'); // Remove leading 0 for API
 $chargeId = 'CHG_' . time() . '_' . substr(md5(rand()), 0, 6);
 $reference = 'WD_' . time();

 $payload = [
    'amount' => $amount,
    'currency' => 'MWK',
    'mobile' => $paychanguPhone,
    'network' => $network,
    'mobile_money_operator_ref_id' => $OPERATORS[$network],
    'charge_id' => $chargeId,
    'reference' => $reference,
    'mode' => 'live'
];

 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $PAYCHANGU_BASE_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $PAYCHANGU_SECRET_KEY,
    'Content-Type: application/json',
    'Accept: application/json'
]);

 $payResponse = curl_exec($ch);
 $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 $payData = json_decode($payResponse, true);
curl_close($ch);

// Check if PayChangu failed
if ($httpCode >= 400 || (isset($payData['status']) && $payData['status'] === 'error')) {
    $errMsg = isset($payData['message']) ? $payData['message'] : 'Payment provider error';
    echo json_encode(['success' => false, 'error' => $errMsg]);
    exit;
}

// 6. UPDATE SUPABASE (DEDUCT BALANCE & LOG TRANSACTION)
 $newBalance = $currentBalance - $amount;

// A. Deduct Balance
 $patchUrl = $SUPABASE_URL . "/rest/v1/users?phone=eq." . $sessionPhone;
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $patchUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['balance' => $newBalance]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $SUPABASE_KEY",
    "Authorization: Bearer $SUPABASE_KEY",
    "Content-Type: application/json"
]);
curl_exec($ch);
curl_close($ch);

// B. Log Transaction
 $logUrl = $SUPABASE_URL . "/rest/v1/payments";
 $logPayload = [
    'charge_id' => $chargeId,
    'phone' => $cleanPhone,
    'amount' => $amount,
    'type' => 'withdrawal',
    'status' => 'success',
    'provider' => $network,
    'created_at' => date('c')
];

 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $logUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($logPayload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $SUPABASE_KEY",
    "Authorization: Bearer $SUPABASE_KEY",
    "Content-Type: application/json"
]);
curl_exec($ch);
curl_close($ch);

// 7. RETURN SUCCESS
echo json_encode([
    'success' => true,
    'message' => "MWK $amount sent successfully!",
    'new_balance' => $newBalance,
    'transaction' => [
        'id' => $reference,
        'phone' => $cleanPhone
    ],
    'instructions' => [
        "✅ MWK $amount sent to $cleanPhone",
        "📱 Via " . strtoupper($network) . " Money",
        "⏳ Wait 2-5 minutes"
    ]
]);
?>