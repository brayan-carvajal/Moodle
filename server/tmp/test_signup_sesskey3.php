<?php
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

// Extract sesskey from hidden input
preg_match('/<input[^>]*name="sesskey"[^>]*value="([^"]+)"[^>]*>/', $response, $matches);
$sesskey = $matches[1] ?? '';

echo "Sesskey from hidden input: '$sesskey'\n";

// Also extract from M.cfg
preg_match('/"sesskey":"([^"]+)"/', $response, $matches2);
$sesskey_js = $matches2[1] ?? '';
echo "Sesskey from JS: '$sesskey_js'\n";

// Use JS sesskey for POST
$sesskey = $sesskey_js ?: $sesskey;

// POST with sesskey
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'sesskey' => $sesskey,
    '_qf__login_signup_form' => '1',
    'username' => 'testuser123',
    'password' => 'TestPass123!',
    'email' => 'test@example.com',
    'email2' => 'test@example.com',
    'firstname' => 'Test',
    'lastname' => 'User',
    'city' => 'TestCity',
    'country' => 'CO',
    'lang' => 'es',
    'submitbutton' => 'Crear cuenta',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nPOST status: $code\n";

if ($code != 200) {
    echo "Response body:\n";
    echo substr($response, 0, 1000);
}
