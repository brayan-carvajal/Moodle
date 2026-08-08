<?php
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP status: $code\n\n";

// Find all form tags
preg_match_all('/<form[^>]*>/i', $response, $matches);
echo "Forms found: " . count($matches[0]) . "\n";
foreach ($matches[0] as $form) {
    echo "  $form\n";
}

// Find the signup form specifically
$pos = strpos($response, 'login_signup_form');
if ($pos !== false) {
    echo "\n=== Signup form context ===\n";
    echo substr($response, $pos - 200, 800);
}
