<?php
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'sesskey' => 'AmTOYqINeX',
    'username' => 'testuser123',
    'password' => 'TestPass123!',
    'firstname' => 'Test',
    'lastname' => 'User',
    'email' => 'test@example.com',
    'city' => 'TestCity',
    'country' => 'CO',
    'lang' => 'es',
    'recaptcha_token' => '',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);
echo "POST status: $code\n";
echo "Redirect URL: " . ($redirect ?: 'none') . "\n";