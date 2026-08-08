<?php
// First, get the signup page to get cookies and sesskey
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

preg_match('/name="sesskey" value="([^"]+)"/', $response, $matches);
$sesskey = $matches[1] ?? '';

echo "GET status: $code\n";
echo "Sesskey: $sesskey\n\n";

// Now POST with cookies and sesskey
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

echo "POST status: $code\n";

if ($code == 200) {
    echo "SUCCESS! Form submitted.\n";
    echo substr($response, 0, 500);
} else {
    echo "FAILED with status: $code\n";
}
