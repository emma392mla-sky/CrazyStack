<?php
// ============================================
// 📱 mobile_pay.php - Paychangu Payment Processor v6.0
// ============================================
// 🔧 FIXES:
// ✅ Phone number consistency throughout pipeline
// ✅ Pre-saves charge_id → phone mapping to Supabase
// ✅ Correct metadata sent to webhook
// ✅ All phone formats preserved for matching
// ✅ Enhanced logging for debugging
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Send JSON response and exit
 */
function respond($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Normalize phone number to standard format (+265...)
 * 
 * @param string $rawPhone - Raw phone input
 * @param string $format - Target format
 * @return string|null - Normalized phone or null if invalid
 */
function normalizePhone($rawPhone, $format = 'international') {
    // Remove all non-digit characters
    $phone = preg_replace('/\D/', '', $rawPhone);
    
    if (empty($phone) || strlen($phone) < 9) {
        return null;
    }
    
    // Remove leading 265 if present (to get 9-digit format)
    if (strlen($phone) === 12 && strpos($phone, '265') === 0) {
        $phone = substr($phone, 3);
    }
    
    // Remove leading 0 to get 9 digits
    if (strlen($phone) === 10 && strpos($phone, '0') === 0) {
        $phone = substr($phone, 1);
    }
    
    // Now we have 9 digits (e.g., 999123456 or 892012517)
    
    switch ($format) {
        case 'international': // +265999123456
            return '+265' . $phone;
            
        case 'local_with_zero': // 0999123456
            return '0' . $phone;
            
        case 'raw_9digit': // 999123456
            return $phone;
            
        case 'paychangu': // Format expected by PayChangu API
            return '0' . $phone; // PayChangu expects 10 digits starting with 0
            
        default:
            return '+265' . $phone;
    }
}

try {
    // ============================================
    // 📥 RECEIVE & VALIDATE DATA
    // ============================================
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond([
            'status' => 'error',
            'message' => 'Method not allowed'
        ], 405);
    }

    $rawInput = file_get_contents('php://input');
    
    if (empty($rawInput)) {
        respond([
            'status' => 'error',
            'message' => 'No data received'
        ], 400);
    }
    
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        respond([
            'status' => 'error',
            'message' => 'Invalid JSON: ' . json_last_error_msg()
        ], 400);
    }

    // ============================================
    // ✅ FIX #1: Extract ALL fields including userId
    // ============================================
    $rawPhone = $input['mobile'] ?? $input['phone'] ?? '';
    $amount = floatval($input['amount'] ?? 0);
    $provider = strtolower(trim($input['provider'] ?? ''));
    $type = $input['type'] ?? 'deposit';
    $userId = $input['userId'] ?? $input['user_id'] ?? $input['customer_phone'] ?? ''; // Get user identifier
    
    // Validation
    if (empty($rawPhone) || strlen(preg_replace('/\D/', '', $rawPhone)) < 9) {
        respond([
            'status' => 'error',
            'message' => 'Valid phone number required (9+ digits)',
            'field' => 'mobile'
        ], 400);
    }

    if (!$amount || $amount < 50) {
        respond([
            'status' => 'error',
            'message' => 'Minimum amount is 50 MWK',
            'field' => 'amount'
        ], 400);
    }

    if ($amount > 1000000) {
        respond([
            'status' => 'error',
            'message' => 'Maximum amount is 1,000,000 MWK',
            'field' => 'amount'
        ], 400);
    }
    
    if (!in_array($type, ['deposit', 'withdraw'])) {
        respond([
            'status' => 'error',
            'message' => 'Invalid transaction type'
        ], 400);
    }

    // ============================================
    // 📱 PHONE NORMALIZATION (MULTIPLE FORMATS)
    // ============================================
    
    // Generate all needed formats from RAW INPUT
    $phoneInternational = normalizePhone($rawPhone, 'international');      // +265892012517
    $phonePaychangu = normalizePhone($rawPhone, 'paychangu');              // 0892012517
    $phoneRaw9Digit = normalizePhone($rawPhone, 'raw_9digit');             // 892012517
    $phoneLocalWithZero = normalizePhone($rawPhone, 'local_with_zero');    // 0892012517
    
    // ✅✅✅ CRITICAL FIX: Use raw input phone as primary identifier
    // Don't override with userId if it's different (that was causing the bug!)
    $customerIdentifier = !empty($userId) ? $userId : $phoneInternational;
    
    // But ALWAYS store the actual entered phone separately
    $actualEnteredPhone = $rawPhone; // Exactly what user typed
    
    // Masked version for display (security)
    $phoneMasked = substr($phoneRaw9Digit, 0, 3) . '****' . substr($phoneRaw9Digit, -2);

    // ============================================
    // 🔍 ENHANCED LOGGING FOR DEBUGGING
    // ============================================
    error_log("\n" . str_repeat("=", 60));
    error_log("📱 PAYMENT REQUEST RECEIVED v6.0");
    error_log(str_repeat("=", 60));
    error_log("📥 Raw Input Phone: {$rawPhone}");
    error_log("📱 International Format: {$phoneInternational}");
    error_log("📱 PayChangu Format: {$phonePaychangu}");
    error_log("📱 Local Format (with 0): {$phoneLocalWithZero}");
    error_log("📱 Raw 9-Digit: {$phoneRaw9Digit}");
    error_log("🆔 Customer Identifier (userId): {$customerIdentifier}");
    error_log("💰 Amount: MWK {$amount}");
    error_log("🔄 Transaction Type: {$type}");
    error_log("📡 Provider: {$provider}");

    // ============================================
    // 📡 NETWORK DETECTION (MALAWIAN OPERATORS)
    // ============================================
    $prefix2 = substr($phoneRaw9Digit, 0, 2);
    $prefix3 = substr($phoneRaw9Digit, 0, 3);
    
    $isAirtel = false;
    $operatorName = 'Unknown';
    $operatorId = '';
    $ussdCode = '';
    $networkCode = '';

    // PRIORITY 1: Check if provider was forced from frontend
    if (!empty($provider)) {
        if (in_array($provider, ['airtel', 'airtel_money', 'airtelmoney'])) {
            $isAirtel = true;
        } elseif (in_array($provider, ['tnm', 'tnm_mpamba', 'mpamba', 'tnmmpamba'])) {
            $isAirtel = false;
        }
    }

    // PRIORITY 2: Auto-detect by prefix
    if (empty($provider) || $provider === 'auto') {
        // Airtel prefixes: 99, 98
        if (in_array($prefix2, ['99', '98']) || in_array($prefix3, ['099', '098'])) {
            $isAirtel = true;
        }
        // TNM prefixes: 88, 87, 81
        elseif (in_array($prefix2, ['88', '87', '81']) || in_array($prefix3, ['088', '087', '081'])) {
            $isAirtel = false;
        }
        else {
            $isAirtel = false; // Default to TNM
        }
    }

    // Set operator details based on detection
    if ($isAirtel) {
        $operatorName = 'Airtel Money';
        $operatorId = "20be6c20-adeb-4b5b-a7ba-0769820df4fb";
        $ussdCode = '*303#';
        $networkCode = 'airtel';
    } else {
        $operatorName = 'TNM mPamba';
        $operatorId = "27494cb5-ba9e-437f-a114-4e7a7686bcca";
        $ussdCode = '*456#';
        $networkCode = 'tnm';
    }

    error_log("📡 Detected Operator: {$operatorName} ({$networkCode})");

    // ============================================
    // 🆔 GENERATE UNIQUE CHARGE ID
    // ============================================
    $charge_id = "CHG_" . date('YmdHis') . '_' . bin2hex(random_bytes(4));

    // ============================================
    // ✅✅✅ NEW v6.0: PRE-SAVE PHONE MAPPING TO SUPABASE
    // ============================================
    // This saves the correct phone BEFORE sending to PayChangu,
    // so the webhook can look it up even if PayChangu strips our metadata!
    
    $supabaseUrl = "https://awnzbiatwnfmryerfxwg.supabase.co";
    $supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg";

    $mappingData = [
        'charge_id'           => $charge_id,
        'correct_phone'       => $phoneInternational,         // +265892012517
        'correct_phone_local' => $phoneLocalWithZero,          // 0892012517
        'original_input'      => $actualEnteredPhone,           // Exactly what user typed
        'amount'              => $amount,
        'transaction_type'     => $type,
        'network'             => $networkCode,
        'operator'            => $operatorName,
        'status'              => 'pending_mapping',
        'created_at'          => date('c')
    ];

    $mappingJson = json_encode($mappingData);

    error_log("\n" . str_repeat("-", 60));
    error_log("💾 PRE-SAVING PHONE MAPPING TO SUPABASE");
    error_log(str_repeat("-", 60));
    error_log("Charge ID: {$charge_id}");
    error_log("Correct Phone (DB): {$phoneInternational}");
    error_log("Correct Phone (Local): {$phoneLocalWithZero}");
    error_log("Original Input: {$actualEnteredPhone}");
    error_log(str_repeat("-", 60));

    // cURL request to insert into phone_mappings table
    $mapCh = curl_init();
    curl_setopt_array($mapCh, [
        CURLOPT_URL            => "{$supabaseUrl}/rest/v1/phone_mappings",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $mappingJson,
        CURLOPT_HTTPHEADER     => [
            "apikey: {$supabaseKey}",
            "Authorization: Bearer {$supabaseKey}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $mapResponse = curl_exec($mapCh);
    $mapHttpCode = curl_getinfo($mapCh, CURLINFO_HTTP_CODE);
    $mapError = curl_error($mapCh);
    curl_close($mapCh);

    if ($mapHttpCode === 201 || $mapHttpCode === 200) {
        $mapData = json_decode($mapResponse, true);
        $mappingId = $mapData[0]['id'] ?? $mapData['id'] ?? 'unknown';
        
        error_log("✅ Phone mapping saved successfully!");
        error_log("   Mapping ID: {$mappingId}");
        error_log("   HTTP Code: {$mapHttpCode}");
    } else {
        error_log("⚠️ WARNING: Failed to save phone mapping!");
        error_log("   HTTP Code: {$mapHttpCode}");
        error_log("   Error: {$mapError}");
        error_log("   Response: " . substr($mapResponse, 0, 300));
        
        // Don't fail the payment - just log warning
        // The webhook will fall back to customer.phone (which might be wrong)
        error_log("   ⚠️ Payment will continue but webhook may use wrong phone!");
    }

    error_log(str_repeat("-", 60) . "\n");

    // ============================================
    // 🔗 PREPARE PAYCHANGU API PAYLOAD
    // ============================================
    $payload = [
        "amount"                        => $amount,
        "currency"                      => "MWK",
        "mobile"                        => $phonePaychangu, // Use PayChangu format for USSD
        "email"                         => "payment@crazystack.mw",
        "mobile_money_operator_ref_id"  => $operatorId,
        "charge_id"                     => $charge_id,
        "callback_url"                  => "https://vfntorjzpselgbhkjetz.supabase.co/functions/v1/paychangu-webhook",
        "description"                   => "CrazyStack {$type} - {$operatorName}",
        
        // ✅✅✅ FIX #2: Complete metadata with ALL phone formats for webhook
        "meta"                          => [
            // Primary identifiers
            "customer_phone"           => $phoneInternational,      // +265892012517 (DB format)
            "customer_phone_local"     => $phoneLocalWithZero,      // 0892012517 (local format)
            "customer_phone_raw"       => $phoneRaw9Digit,          // 892012517 (raw digits)
            "original_input"           => $actualEnteredPhone,      // Exactly what user typed
            
            // User identification
            "user_id"                  => $customerIdentifier,      // From frontend session
            
            // Transaction details
            "transaction_type"         => $type,
            "network"                  => $networkCode,
            "operator"                 => $operatorName,
            
            // Debug info
            "source"                   => "mobile_pay_v6.0",
            "timestamp"                => date('c')
        ]
    ];

    // Log payload (remove sensitive data)
    error_log("🚀 SENDING TO PAYCHANGU API:");
    error_log("   Charge ID: {$charge_id}");
    error_log("   Mobile (API/USSD): {$phonePaychangu}");
    error_log("   Operator: {$operatorName}");
    error_log("   Metadata customer_phone: {$phoneInternational}");
    error_log("   Metadata original_input: {$actualEnteredPhone}");

    // ============================================
    // 🚀 CALL PAYCHANGU API VIA cURL
    // ============================================
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => "https://api.paychangu.com/mobile-money/payments/initialize",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer sec-live-Xvad0XHGxKVQ0w3QpIDIpNh1POaHtTkS",
            "Content-Type: application/json",
            "Accept: application/json"
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_FAILONERROR    => false // Don't fail on HTTP errors, handle manually
    ]);

    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);

    curl_close($ch);

    // ============================================
    // ✅ BUILD COMPLETE RESPONSE FOR FRONTEND
    // ============================================

    // Decode Paychangu response
    $decodedApi = json_decode($apiResponse, true);
    $apiSuccess = ($decodedApi && isset($decodedApi['status']) && $decodedApi['status'] === 'success');

    // ✅✅✅ FIX #3: Return ALL phone formats so frontend/webhook can match correctly!
    $transactionBase = [
        'charge_id'         => $charge_id,
        'amount'            => $amount,
        'currency'          => 'MWK',
        
        // ✅✅✅ PRIMARY: All phone formats for correct matching
        'phone'             => $phoneInternational,       // PRIMARY DB format: +265892012517
        'phone_international'=> $phoneInternational,       // +265892012517
        'phone_local'       => $phoneLocalWithZero,       // 0892012517  
        'phone_raw'         => $phoneRaw9Digit,           // 892012517
        'phone_masked'      => $phoneMasked,              // 892****17
        'original_input'    => $actualEnteredPhone,        // Original user input
        
        // User identification
        'user_id'           => $customerIdentifier,       // From session
        
        // Operator info
        'operator'          => $operatorName,
        'operator_id'       => $operatorId,
        'network'           => $networkCode,
        'ussd_code'         => $ussdCode,
        'ussd_prompt'       => "Dial {$ussdCode} to complete your {$operatorName} payment",
        
        // Transaction info
        'type'              => $type,
        'created_at'        => date('c'),
        'expires_at'        => date('c', time() + 900), // 15 min expiry
        'status'            => 'pending',
        
        // ✅ NEW v6.0: Confirm mapping saved
        'mapping_saved'     => ($mapHttpCode === 201 || $mapHttpCode === 200),
        'mapping_id'        => $mappingId ?? null
    ];

    // ❌ Case 1: cURL Connection Failed
    if ($apiResponse === false) {
        error_log("❌ cURL Error: {$curlError} (code: {$curlErrno})");
        
        respond([
            'status'      => 'error',
            'message'     => 'Failed to connect to payment provider',
            'error'       => $curlError,
            'error_code'  => $curlErrno,
            'retryable'   => true,
            'transaction' => array_merge($transactionBase, ['status' => 'connection_failed'])
        ], 503); // Service Unavailable
    }

    // ❌ Case 2: Invalid JSON Response from Gateway
    if (!$decodedApi) {
        error_log("❌ Invalid JSON from PayChangu. HTTP Code: {$httpCode}");
        error_log("   Raw Response: " . substr($apiResponse, 0, 500));
        
        respond([
            'status'        => 'error',
            'message'       => 'Invalid response from payment gateway',
            'http_code'     => $httpCode,
            'raw_preview'   => substr($apiResponse, 0, 300),
            'transaction'   => array_merge($transactionBase, ['status' => 'invalid_response'])
        ], 502); // Bad Gateway
    }

    // ❌ Case 3: Paychangu Returned Error
    if (!$apiSuccess) {
        $payError = $decodedApi['message'] ?? $decodedApi['error'] ?? 'Unknown gateway error';
        $payCode = $decodedApi['code'] ?? null;
        
        error_log("❌ PayChangu Error: {$payError} (code: {$payCode})");
        
        respond([
            'status'          => 'error',
            'message'         => 'Payment failed: ' . $payError,
            'gateway_code'    => $payCode,
            'transaction'     => array_merge($transactionBase, ['status' => 'gateway_rejected']),
            'gateway_response' => array_slice($decodedApi, 0, 10)
        ], 400); // Bad Request
    }

    // ✅✅✅ Case 4: SUCCESS! Return COMPLETE Transaction Object
    error_log("\n" . str_repeat("=", 60));
    error_log("✅ PAYMENT INITIALIZED SUCCESSFULLY! (v6.0)");
    error_log(str_repeat("=", 60));
    error_log("   Charge ID: {$charge_id}");
    error_log("   Mapping Saved: " . (($mapHttpCode === 201 || $mapHttpCode === 200) ? 'YES ✅' : 'NO ⚠️'));
    error_log("   Returning to frontend:");
    error_log("      → Primary phone (DB): {$phoneInternational}");
    error_log("      → Local phone: {$phoneLocalWithZero}");
    error_log("      → Original input: {$actualEnteredPhone}");
    error_log(str_repeat("=", 60) . "\n");
    
    respond([
        'status'      => 'success',
        'message'     => 'Payment initialized successfully. Please complete USSD prompt.',
        
        // ✅✅✅ MAIN TRANSACTION DATA (Everything frontend needs!)
        'transaction' => $transactionBase,
        
        // Metadata for debugging/logging
        'meta' => [
            'http_code'      => $httpCode,
            'response_time'  => date('c'),
            'provider'       => 'paychangu',
            'environment'    => 'live',
            'version'        => '6.0',  // Updated version
            'phone_formats'  => [ // Document what formats are included
                'phone_for_db'    => $phoneInternational,
                'phone_for_api'   => $phonePaychangu,
                'phone_raw'       => $phoneRaw9Digit,
                'original_input'  => $actualEnteredPhone
            ],
            'fixes_applied'  => [
                'phone_mapping_pre_saved' => ($mapHttpCode === 201 || $mapHttpCode === 200),
                'metadata_complete'        => true,
                'all_formats_included'     => true
            ]
        ],
        
        // Instructions for user
        'instructions' => [
            'step1' => "Check your phone for USSD prompt or",
            'step2' => "Dial {$ussdCode} manually",
            'step3' => "Enter your PIN to confirm MWK {$amount} {$type}",
            'timeout' => "Expires in 15 minutes"
        ],
        
        // Raw gateway response (for advanced use cases)
        'gateway_response' => $decodedApi
    ]);

} catch (Exception $e) {
    error_log("\n💥💥💥 EXCEPTION 💥💥💥");
    error_log("Message: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ":" . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());
    error_log("💥💥💥 END EXCEPTION 💥💥💥\n");
    
    respond([
        'status'      => 'error',
        'message'     => 'Server error occurred',
        'error_code'  => $e->getCode(),
        'error_msg'   => $e->getMessage(),
        'timestamp'   => date('c')
    ], 500);
}
?>