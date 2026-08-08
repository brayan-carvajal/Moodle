<?php
define('CLI_SCRIPT', true);
require_once __DIR__ . '/../moodle/config.php';
global $DB, $CFG;

// Check current auth plugins
$auths = $DB->get_records('config', ['name' => 'registerauth']);
foreach ($auths as $a) {
    echo "registerauth: " . $a->value . PHP_EOL;
}

// Check if email confirmation is required
$confirm = $DB->get_record('config', ['name' => 'registerauth_selfconfirmation']);
if ($confirm) {
    echo "Self confirmation: " . $confirm->value . PHP_EOL;
} else {
    echo "Self confirmation setting not found" . PHP_EOL;
}

// Check email auth settings
$emailauth = $DB->get_record('config', ['name' => 'auth_email_enabled']);
if ($emailauth) {
    echo "Email auth enabled: " . $emailauth->value . PHP_EOL;
}

// Check for auth_email settings
$settings = $DB->get_records('config', [], 'name ASC', 'name, value');
foreach ($settings as $s) {
    if (strpos($s->name, 'email') !== false || strpos($s->name, 'auth') !== false || strpos($s->name, 'smtp') !== false || strpos($s->name, 'mail') !== false) {
        echo $s->name . " = " . $s->value . PHP_EOL;
    }
}
