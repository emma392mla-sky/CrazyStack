<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

 $input = json_decode(file_get_contents("php://input"), true);

// =============================================
// 1. GET INPUTS
// =============================================
 $phone = $input['mobile'] ?? null;
 $amount = $input['amount'] ?? null;

if (!$phone || !$amount) {
    echo json_encode(["status" => "error", "message" => "Phone and amount required"]);
    exit;
}

// =============================================
// 2. NORMALIZE PHONE NUMBER
// =============================================
// Remove non-digits
 $phone = preg_replace('/[^0-9]/', '', $phone);

// Remove +265 or 265 prefix
if (strlen($phone) > 9 && strpos($phone, '265') === 0) {
    $phone = substr($phone, 3);
}

// Ensure it starts with 0
if (strlen($phone) === 9 && strpos($phone, '0') !== 0) {
    $phone = '0' . $phone;
}

// =============================================
// 3. DETECT NETWORK OPERATOR (CORRECTED!)
// =============================================
 $network = "";
 $operator_id = "";

/*
 * MALAWI MOBILE NETWORK PREFIXES:
 * 
 * AIRTEL MONEY:  099xxxxxxx, 089xxxxxxx
 * TNM MPAMBA:    088xxxxxxx
 */

if (strpos($phone, '089') === 0 || strpos($phone, '099') === 0) {
    // Airtel Money
    $network = "Airtel";
    $operator_id = "20be6c20-adeb-4b5b-a7ba-0769820df4fb";
    
} elseif (strpos($phone, '088') === 0) {
    // TNM mPamba
    $network = "TNM";
    $operator_id = "27494cb5-ba9e-437f-a114-4e7a7686bcca";
    
} else {
    // Unknown network
    echo json_encode([
        "status" => "error", 
        "message" => "Unsupported phone number. Use Airtel (089/099) or TNM (088)."
    ]);
    exit;
}

// =============================================
// 4. PREPARE PAYLOAD
// =============================================
 $unique_id = "WD_" . date('YmdHis') . rand(100,999); // Better unique ID format
 $apiKey = "sec-live-Xvad0XHGxKVQ0w3QpIDIpNh1POaHtTkS";

 $data = [
    "mobile_money_operator_ref_id" => $operator_id,
    "mobile" => $phone,
    "amount" => (float)$amount,
    "charge_id" => $unique_id
];

// =============================================
// 5. CALL PAYCHANGU PAYOUT API
// =============================================
 $options = [
    "http" => [
        "header"  => 
            "Authorization: Bearer " . $apiKey . "\r\n" .
            "Content-Type: application/json\r\n" .
            "Accept: application/json\r\n",
        "method"  => "POST",
        "content" => json_encode($data),
        "ignore_errors" => true,
        "timeout" => 30  // Added timeout
    ]
];

 $context = stream_context_create($options);

// PAYOUT ENDPOINT (FOR WITHDRAWALS - SENDING MONEY TO USER)
 $response = @file_get_contents(
    "https://api.paychangu.com/mobile-money/payouts/initialize", 
    false, 
    $context
);

// =============================================
// 6. HANDLE RESPONSE / ERRORS
// =============================================
if ($response === false) {
    $error = error_get_last();
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . ($error['message'] ?? 'Unknown error')
    ]);
    exit;
}

// Return actual Paychangu response
echo $response;

?>