<?php
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Search for sesskey in various formats
$patterns = [
    'name="sesskey" value="([^"]+)"',
    'sesskey.*?([a-zA-Z0-9]{20,})',
    'M\.cfg.*?sesskey.*?:\s*"([^"]+)"',
];

foreach ($patterns as $pattern) {
    if (preg_match($pattern, $response, $matches)) {
        echo "Pattern '$pattern' matched: " . $matches[1] . "\n";
    }
}

// Also search for any hidden input fields
preg_match_all('/<input[^>]*type="hidden"[^>]*>/i', $response, $hidden);
echo "\nHidden inputs:\n";
foreach ($hidden[0] as $input) {
    echo "  $input\n";
}

// Check for any JavaScript that might set the sesskey
if (preg_match('/sesskey.*?var\s+\w+\s*=\s*["\']([^"\']+)["\']/', $response, $matches)) {
    echo "\nJS sesskey: " . $matches[1] . "\n";
}
