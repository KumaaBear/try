<?php

$url = "https://api.mandbox.com/apitest/v1/contact.php?action=view";

// Initialize cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [], // Required even for view
    CURLOPT_SSL_VERIFYPEER => false, // Needed for localhost
]);

$response = curl_exec($curl);

// Get HTTP response code and cURL error (if any)
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
curl_close($curl);

// Output debug information
echo "<h3>Debug Output</h3>";
echo "<strong>HTTP Status:</strong> $httpCode<br>";
echo "<strong>cURL Error:</strong> $error<br>";
echo "<strong>Raw Response:</strong><br><pre>$response</pre>";

// Try to decode JSON
$data = json_decode($response, true);
echo "<h4>Decoded JSON:</h4><pre>";
print_r($data);
echo "</pre>";

?>
