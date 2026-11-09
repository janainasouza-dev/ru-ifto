<?php
// ============================================
// CONFIGURAÇÃO GERAL — RU IFTO
// ============================================

date_default_timezone_set('America/Araguaina');

// Banco de dados (ajuste conforme seu ambiente; XAMPP padrão: root sem senha)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'ruifto');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Regras de negócio
const PRECO_REFEICAO_VISITANTE = 13.00;
const CANCELAMENTO_HORA   = 9;   // prazo: até 09:30
const CANCELAMENTO_MINUTO = 30;

define('ROOT_PATH', dirname(__DIR__));

// URL base do projeto (ex.: /ruifto), detectada automaticamente
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ''), '/');
$root    = str_replace('\\', '/', ROOT_PATH);
define('BASE_URL', $docRoot !== '' && str_starts_with($root, $docRoot) ? substr($root, strlen($docRoot)) : '');

// Autoload das classes em /src
spl_autoload_register(function (string $classe) {
    $arquivo = ROOT_PATH . '/src/' . $classe . '.php';
    if (is_file($arquivo)) {
        require_once $arquivo;
    }
});

require_once ROOT_PATH . '/src/helpers.php';

// Sessão segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
