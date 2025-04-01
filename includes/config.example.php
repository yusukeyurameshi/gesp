<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'gesp');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Configurações de upload
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);

// Configurações de segurança
define('HASH_COST', 12);
define('SESSION_LIFETIME', 3600); // 1 hora 



try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Configurações gerais
define('SITE_NAME', 'GESP - Gestão de Estoque do Almoxarifado');
define('SITE_URL', 'http://seusite.com/gesp');

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

// Função para redirecionar para a página de login se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
} 