<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// --- Helper ---
function respond($status, $message, $extra = []) {
    echo json_encode(array_merge(["status" => $status, "message" => $message], $extra));
    exit;
}

// --- Get raw request ---
$request = file_get_contents('php://input');
file_put_contents("payload_debug.log", date('Y-m-d H:i:s') . " | " . $request . "\n", FILE_APPEND);

// --- Decode JSON ---
$data = json_decode($request, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    respond("error", "Invalid JSON: " . json_last_error_msg());
}

// --- Extract safely ---
$merchantTrxId = $data['merchantTrxId'] ?? uniqid("trx_");
$wallet = trim($data['wallet'] ?? "");
$bankId = (int)($data['bankId'] ?? 0);
$amount = (float)($data['amount'] ?? 0);

// --- Validate input ---
if (empty($wallet) || $bankId === 0 || $amount <= 0) {
    respond("error", "Invalid input: missing wallet, bankId, or amount");
}

// --- Malipo credentials ---
$apiKey = "bf2d77cc1bfde94da7ba7364ad13fa58";
$appId = "945454610";
$url = "https://app.malipo.mw/api/v1/payments/withdrawal";

// --- Prepare payload ---
$payload = [
    "merchantTrxId" => $merchantTrxId,
    "wallet" => $wallet,
    "bankId" => $bankId,
    "amount" => number_format($amount, 2, '.', '') // format like "50.00"
];

// --- Log payload ---
file_put_contents("payload_debug.log", date('Y-m-d H:i:s') . " | FINAL PAYLOAD: " . json_encode($payload) . "\n", FILE_APPEND);

// --- cURL ---
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json; charset=utf-8",
        "Accept: application/json",
        "x-api-key: $apiKey",
        "x-app-id: $appId"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// --- Handle cURL error ---
if ($curlError) {
    respond("error", "Network error. Please try again later.");
}

// --- Log Malipo response ---
file_put_contents("malipo_response.log", date('Y-m-d H:i:s') . " | HTTP $httpCode | $response\n", FILE_APPEND);

// --- Decode Malipo JSON ---
$responseData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    respond("error", "Unexpected server response. Please try again later.");
}

// --- Handle Malipo Errors ---
if ($httpCode < 200 || $httpCode >= 300) {

    // Check for "Insufficient balance" message
    $message = $responseData['message'] ?? '';

    $timestamp = date('j M Y, g:i A');
    $logLine = sprintf(
        "❌ [%s] Wallet: %s | TrxID: %s | HTTP %d | %s\n",
        $timestamp,
        $wallet,
        $merchantTrxId,
        $httpCode,
        $message
    );
    file_put_contents("cashout_error.log", $logLine, FILE_APPEND);

    // Hide sensitive info from users
    if (stripos($message, 'insufficient balance') !== false) {
        respond("error", "⚠️ Please try again later.");
    } else {
        respond("error", "⚠️ Transaction could not be completed. Please try again later.");
    }
}

// --- Success case ---
if (isset($responseData['status']) && strtolower($responseData['status']) === 'success') {
    $timestamp = date('j M Y, g:i A');
    $successLine = sprintf(
        "Cashout successful of MWK %.2f for user %s (TransID: %s) on %s\n",
        $amount,
        $wallet,
        $merchantTrxId,
        $timestamp
    );
    file_put_contents("cashout_success.log", $successLine, FILE_APPEND);
    $responseData['status'] = 'Completed';
}

// --- Return response to frontend ---
echo json_encode($responseData);
?>
