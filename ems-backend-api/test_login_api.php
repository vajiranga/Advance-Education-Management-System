<?php

function test_api_login($url, $data) {
    echo "------------------------------------------------\n";
    echo "Testing Login to: $url\n";
    echo "Data: " . json_encode($data) . "\n";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    
    if ($httpCode == 200) {
        echo "[SUCCESS] Login API is working.\n";
    } else {
        echo "[FAILED] Login API rejected credentials or errored.\n";
    }
}

// 1. Test Admin Login
test_api_login('http://localhost:8000/api/login', [
    'email' => 'admin',
    'password' => '12345678'
]);

// 2. Test Student Login
test_api_login('http://localhost:8000/api/login', [
    'email' => 'STU20266784',
    'password' => '12345678'
]);

// 3. Test Teacher Login
test_api_login('http://localhost:8000/api/login', [
    'email' => 'TCH20261608',
    'password' => '12345678'
]);
