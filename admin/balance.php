<?php
// Set the header to return JSON so JavaScript can read it
header('Content-Type: application/json');

// 1. Your PayChangu Secret Key
 $secretKey = 'sec-live-Xvad0XHGxKVQ0w3QpIDIpNh1POaHtTkS';

// 2. The Currency you want to query
 $currency = 'MWK';

// 3. The API Endpoint
 $url = "https://api.paychangu.com/wallet-balance?currency=" . $currency;

// 4. Initialize cURL session
 $ch = curl_init();

// 5. Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer ' . $secretKey
]);

// 6. Execute the request and capture the response
 $response = curl_exec($ch);

// 7. Check for cURL errors
if (curl_errno($ch)) {
    // Return error as JSON
    echo json_encode(['status' => 'error', 'message' => curl_error($ch)]);
} else {
    // 8. Close the connection
    curl_close($ch);

    // 9. Decode the JSON response into a PHP array
    $result = json_decode($response, true);

    // 10. Check if the API returned a success status
    if (isset($result['status']) && $result['status'] === 'success') {
        
        $data = $result['data'];
        
        // Return the balance details as a clean JSON object
        echo json_encode([
            'status' => 'success',
            'environment' => $data['environment'],
            'currency' => $data['currency'],
            'main_balance' => $data['main_balance'],
            'collection_balance' => $data['collection_balance']
        ]);
        
    } else {
        // Handle API Error
        $message = $result['message'] ?? 'Unknown error occurred';
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
}
?>