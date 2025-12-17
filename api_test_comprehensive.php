<?php
// Comprehensive API Testing Script
echo "=== Truckers Africa API Testing ===\n\n";

// Test endpoints to check
$endpoints = [
    // Public endpoints that should work without authentication
    'GET /api/v1/test' => 'http://localhost/truckers-africa/index.php/api/v1/test',
    'GET /api/v1/currencies' => 'http://localhost/truckers-africa/index.php/api/v1/currencies',
    'GET /api/v1/services/categories' => 'http://localhost/truckers-africa/index.php/api/v1/services/categories',
    'GET /api/v1/services/nearby' => 'http://localhost/truckers-africa/index.php/api/v1/services/nearby?lat=-26.2041&lng=28.0473',
    
    // Legacy endpoints
    'GET /api/nearby-merchants' => 'http://localhost/truckers-africa/index.php/api/nearby-merchants?lat=-26.2041&lng=28.0473',
    
    // Direct controller access (bypass routing)
    'Direct test' => 'http://localhost/truckers-africa/test_api.php',
    'Direct test 2' => 'http://localhost/truckers-africa/direct_test.php',
];

function testEndpoint($name, $url) {
    echo "Testing: $name\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
    curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ CURL Error: $error\n";
        return false;
    }
    
    // Split headers and body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "HTTP Code: $httpCode\n";
    
    if ($httpCode == 200) {
        echo "✅ SUCCESS\n";
        echo "Response preview: " . substr(trim($body), 0, 200) . "\n";
    } elseif ($httpCode == 302) {
        echo "⚠️  REDIRECT DETECTED\n";
        // Extract Location header
        if (preg_match('/Location: (.+)/i', $headers, $matches)) {
            echo "Redirecting to: " . trim($matches[1]) . "\n";
        }
    } else {
        echo "❌ FAILED\n";
        echo "Headers:\n" . substr($headers, 0, 300) . "\n";
        echo "Body preview: " . substr(trim($body), 0, 200) . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    return $httpCode == 200;
}

// Test all endpoints
$successCount = 0;
$totalCount = count($endpoints);

foreach ($endpoints as $name => $url) {
    if (testEndpoint($name, $url)) {
        $successCount++;
    }
    sleep(1); // Brief pause between requests
}

echo "=== SUMMARY ===\n";
echo "Successful: $successCount/$totalCount\n";
echo "Failed: " . ($totalCount - $successCount) . "/$totalCount\n";

if ($successCount == 0) {
    echo "\n⚠️  WARNING: All API endpoints failed. This suggests a global redirect issue.\n";
} elseif ($successCount < $totalCount) {
    echo "\n⚠️  Some endpoints are failing. Check individual results above.\n";
} else {
    echo "\n✅ All API endpoints are working correctly!\n";
}
?>
