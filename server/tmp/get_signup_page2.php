<?php
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP status: $code\n\n";

// Find form action
if (preg_match('/<form[^>]*action="([^"]*)"[^>]*>/i', $response, $matches)) {
    echo "Form action: " . $matches[1] . "\n";
}

// Find any JavaScript that might intercept form submission
if (preg_match('/signup.*\.php|signup_form|signupform/i', $response, $matches)) {
    echo "Found signup reference in HTML\n";
}

// Look for the actual signup form HTML
$pos = strpos($response, 'signup');
if ($pos !== false) {
    echo "\n=== Signup context ===\n";
    echo substr($response, $pos, 500);
}
