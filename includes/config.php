<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'gesp');
define('DB_USER', 'almoxarifado');
define('DB_PASS', 'WElcome123@');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Configurações gerais
define('SITE_NAME', 'GESP - Gestão de Estoque do Almoxarifado');
define('SITE_URL', 'http://144.22.168.128/gesp');

// Iniciar sessão
session_start();

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