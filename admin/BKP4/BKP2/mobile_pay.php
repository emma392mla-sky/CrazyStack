<?php
// ============================================
// 📱 mobile_pay.php - Paychangu Payment Processor v3.0
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(0);
ini_set('display_errors', 0);

function respond($data) {
    echo json_encode($data);
    exit;
}

try {
    // ============================================
    // 📥 RECEIVE & VALIDATE DATA
    // ============================================
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    // Extract fields (support both 'phone' and 'mobile')
    $rawPhone = preg_replace('/\D/', '', $input['mobile'] ?? $input['phone'] ?? '');
    $amount = floatval($input['amount'] ?? 0);
    $provider = strtolower(trim($input['provider'] ?? ''));
    $type = $input['type'] ?? 'deposit';

    // Validation
    if (!$rawPhone || strlen($rawPhone) < 9) {
        respond([
            'status' => 'error',
            'message' => 'Valid phone number required (9+ digits)'
        ]);
    }

    if (!$amount || $amount < 50) {
        respond([
            'status' => 'error',
            'message' => 'Minimum amount is 50 MWK'
        ]);
    }

    if ($amount > 1000000) {
        respond([
            'status' => 'error',
            'message' => 'Maximum amount is 1,000,000 MWK'
        ]);
    }

    // ============================================
    // 📱 PHONE NORMALIZATION (MALAWI +265)
    // ============================================
    $phone = $rawPhone;

    // Remove country code +265 if present
    if (strpos($phone, '265') === 0 && strlen($phone) > 9) {
        $phone = substr($phone, 3);
    }

    // Ensure leading 0 for API format (10 digits)
    if (strlen($phone) === 9 && strpos($phone, '0') !== 0) {
        $phone = '0' . $phone;
    }

    // Mask phone for security (show first 3 and last 2)
    //$phoneMasked = substr($phone, 0, 3) . '****' . substr($phone, -2);
     $phoneMasked = $phone;

    // ============================================
    // 📡 NETWORK DETECTION (MALAWIAN OPERATORS)
    // ============================================
    $prefix2 = substr($phone, 0, 2);
    $prefix3 = substr($phone, 0, 3);
    
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
        // Airtel prefixes: 099, 098, 99, 98
        if (in_array($prefix3, ['099', '098']) || in_array($prefix2, ['99', '98'])) {
            $isAirtel = true;
        }
        // TNM prefixes: 088, 087, 081, 88, 87, 81
        elseif (in_array($prefix3, ['088', '087', '081']) || in_array($prefix2, ['88', '87', '81'])) {
            $isAirtel = false;
        }
        // Fallback for unknown prefixes - default to TNM
        else {
            $isAirtel = false;  // TNM is more common in Malawi
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

    // ============================================
    // 🆔 GENERATE UNIQUE CHARGE ID
    // ============================================
    $charge_id = "CHG_" . time() . "_" . mt_rand(1000, 9999);

    // ============================================
    // 🔗 PREPARE PAYCHANGU API PAYLOAD
    // ============================================
    $payload = [
        "amount"                        => $amount,
        "currency"                      => "MWK",
        "mobile"                        => $phone,
        "email"                         => "customer@crazystack.com",
        "mobile_money_operator_ref_id"  => $operatorId,
        "charge_id"                     => $charge_id,
        "callback_url"                  => "https://vfntorjzpselgbhkjetz.supabase.co/functions/v1/paychangu-webhook",
        "description"                   => "CrazyStack {$type} - {$operatorName} - {$charge_id}"
    ];

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
        CURLOPT_FOLLOWLOCATION => false
    ]);

    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    // ============================================
    // ✅ BUILD COMPLETE RESPONSE FOR FRONTEND
    // ============================================

    // Decode Paychangu response
    $decodedApi = json_decode($apiResponse, true);
    $apiSuccess = ($decodedApi && isset($decodedApi['status']) && $decodedApi['status'] === 'success');

    // ❌ Case 1: cURL Connection Failed
    if ($apiResponse === false) {
        respond([
            'status'      => 'error',
            'message'     => 'Failed to connect to payment provider',
            'error'       => $curlError,
            'retryable'   => true,
            
            'transaction' => [
                'charge_id'     => $charge_id,
                'amount'        => $amount,
                'currency'      => 'MWK',
                'phone_masked'  => $phoneMasked,
                'operator'      => $operatorName,
                'network'       => $networkCode,
                'ussd_code'     => $ussdCode,
                'type'          => $type,
                'created_at'    => date('c'),
                'status'        => 'connection_failed'
            ]
        ]);
    }

    // ❌ Case 2: Invalid JSON Response from Gateway
    if (!$decodedApi) {
        respond([
            'status'      => 'error',
            'message'     => 'Invalid response from payment gateway',
            'http_code'   => $httpCode,
            'raw_preview' => substr($apiResponse, 0, 300),
            
            'transaction' => [
                'charge_id'     => $charge_id,
                'amount'        => $amount,
                'currency'      => 'MWK',
                'phone_masked'  => $phoneMasked,
                'operator'      => $operatorName,
                'network'       => $networkCode,
                'ussd_code'     => $ussdCode,
                'type'          => $type,
                'created_at'    => date('c'),
                'status'        => 'invalid_response'
            ]
        ]);
    }

    // ❌ Case 3: Paychangu Returned Error
    if (!$apiSuccess) {
        $payError = $decodedApi['message'] ?? $decodedApi['error'] ?? 'Unknown gateway error';
        
        respond([
            'status'       => 'error',
            'message'      => 'Payment failed: ' . $payError,
            'gateway_code' => $decodedApi['code'] ?? null,
            
            'transaction'  => [
                'charge_id'     => $charge_id,
                'amount'        => $amount,
                'currency'      => 'MWK',
                'phone_masked'  => $phoneMasked,
                'operator'      => $operatorName,
                'network'       => $networkCode,
                'ussd_code'     => $ussdCode,
                'type'          => $type,
                'created_at'    => date('c'),
                'status'        => 'gateway_rejected'
            ],
            
            'gateway_response' => array_slice($decodedApi, 0, 10)
        ]);
    }

    // ✅✅✅ Case 4: SUCCESS! Return COMPLETE Transaction Object
    respond([
        'status'      => 'success',
        'message'     => 'Payment initialized successfully',
        
        // ✅✅✅ MAIN TRANSACTION DATA (Everything frontend needs!)
        'transaction' => [
            'charge_id'         => $charge_id,
            'amount'            => $amount,
            'currency'          => 'MWK',
            'phone_masked'      => $phoneMasked,
            'operator'          => $operatorName,
            'operator_id'       => $operatorId,
            'network'           => $networkCode,
            'ussd_code'         => $ussdCode,
            'ussd_prompt'       => "Dial {$ussdCode} to complete your {$operatorName} payment",
            'description'       => $payload['description'],
            'type'              => $type,
            'created_at'        => date('c'),           // ISO 8601 format
            'expires_at'        => date('c', time() + 900),  // 15 min expiry
            'status'            => 'pending'
        ],
        
        // Metadata for debugging/logging
        'meta' => [
            'http_code'      => $httpCode,
            'response_time'  => date('c'),
            'provider'       => 'paychangu',
            'environment'    => 'live',
            'version'        => '3.0'
        ],
        
        // Raw gateway response (for advanced use cases)
        'gateway_response' => $decodedApi
    ]);

} catch (Exception $e) {
    respond([
        'status'      => 'error',
        'message'     => 'Server error occurred',
        'error_code'  => $e->getCode(),
        'error_msg'   => $e->getMessage(),
        'timestamp'   => date('c')
    ]);
}
?> 