<?php
define('CLI_SCRIPT', true);
require_once __DIR__ . '/../moodle/config.php';
global $DB;

// Opción 2: Deshabilitar confirmación por email para el método de registro
// Esto permite crear cuentas sin envío de email
$auth = $DB->get_record('config', ['name' => 'registerauth']);
if ($auth) {
    echo "Método de registro actual: " . $auth->value . PHP_EOL;
} else {
    echo "No hay método de registro configurado" . PHP_EOL;
}

// Verificar configuración de email
$email = $DB->get_record('config', ['name' => 'smtphosts']);
if ($email) {
    echo "SMTP hosts: " . $email->value . PHP_EOL;
} else {
    echo "SMTP hosts: NO CONFIGURADO" . PHP_EOL;
}

echo PHP_EOL . "Para solucionarlo, ve a:" . PHP_EOL;
echo "1. Administración del sitio > Servidor > Correo electrónico > Configuración SMTP" . PHP_EOL;
echo "   Y configura un servidor SMTP (ej: smtp.gmail.com, smtp.office365.com)" . PHP_EOL;
echo PHP_EOL;
echo "O desactiva la confirmación por email:" . PHP_EOL;
echo "2. Administración del sitio > Plugins > Autenticación > Gestionar autenticación" . PHP_EOL;
echo "   Busca la opción de confirmación por email y desactívala" . PHP_EOL;
