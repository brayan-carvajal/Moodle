<?php
$ch = curl_init('http://localhost/login/signup.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Find the form and all hidden inputs
if (preg_match('/<form[^>]*id="mform1_[^"]*"[^>]*>(.*?)<\/form>/is', $response, $matches)) {
    $form_html = $matches[1];
    
    // Find all input fields
    preg_match_all('/<input[^>]*>/i', $form_html, $inputs);
    echo "Form inputs found: " . count($inputs[0]) . "\n\n";
    
    foreach ($inputs[0] as $input) {
        echo $input . "\n";
    }
}
