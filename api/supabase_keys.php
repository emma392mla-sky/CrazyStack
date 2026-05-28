<?php

// =============================================
// SUPABASE CONFIGURATION
// =============================================

// 1. Main Database Config
 $SUPABASE_PROJECT_URL = "https://awnzbiatwnfmryerfxwg.supabase.co";
 $SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg";

// 2. Payments Database Config
 $PAYMENTS_DB_URL = "https://vfntorjzpselgbhkjetz.supabase.co";
 $PAYMENTS_DB_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZmbnRvcmp6cHNlbGdiaGtqZXR6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzYyNTk4ODAsImV4cCI6MjA5MTgzNTg4MH0._AkGUZJ-D5nsLEfcD1xzbEBEz2KLJdzo3pxuZMLTb4A";

// =============================================
// HELPER FUNCTION TO CALL SUPABASE
// =============================================
function callSupabase($url, $key, $tableName, $method = 'GET', $data = null) {
    $ch = curl_init();
    $fullUrl = $url . '/rest/v1/' . $tableName;

    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    // Headers
    $headers = [
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // If sending data (POST/PUT)
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($response, true);
}

// =============================================
// EXAMPLE USAGE
// =============================================

// 1. Get data from Main DB (Example: 'users' table)
 $mainData = callSupabase($SUPABASE_PROJECT_URL, $SUPABASE_ANON_KEY, 'users');

// 2. Get data from Payments DB (Example: 'transactions' table)
 $paymentData = callSupabase($PAYMENTS_DB_URL, $PAYMENTS_DB_KEY, 'transactions');

// 3. Send the combined result back to your JavaScript
header('Content-Type: application/json');
echo json_encode([
    'main_db_data' => $mainData,
    'payments_db_data' => $paymentData
]);

?>