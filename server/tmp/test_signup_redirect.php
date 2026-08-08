<?php
// Get signup page to get cookies and sesskey
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
$response = curl_exec($ch);
curl_close($ch);

preg_match('/name="sesskey" value="([^"]+)"/', $response, $matches);
$sesskey = $matches[1] ?? '';

echo "Sesskey: $sesskey\n";

// POST with cookies, sesskey, and follow redirects
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
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/Users/BrayanEstivenCarvaja/Downloads/Moodle/server/tmp/cookies.txt');
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirects = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

echo "POST status: $code\n";
echo "Redirects: $redirects\n";
echo "Effective URL: $effective_url\n";

if ($code == 200) {
    echo "SUCCESS!\n";
    echo substr($response, 0, 500);
}
