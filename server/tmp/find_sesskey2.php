<?php
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

// Find the line with sesskey
$lines = explode("\n", $response);
foreach ($lines as $line) {
    if (stripos($line, 'sesskey') !== false) {
        echo "Found: $line\n";
    }
}

// Also check for any session cookie
if (preg_match('/M\.cfg.*?sesskey.*?:\s*"([^"]+)"/', $response, $matches)) {
    echo "\nJS sesskey: " . $matches[1] . "\n";
}
