<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'banco');
define('DB_USER', 'usuario');
define('DB_PASS', 'senha');

// Configurações do site
//define('SITE_NAME', 'Sistema de Almoxarifado');

// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}



// Configurações gerais
define('SITE_NAME', 'GESP - Gestão de Estoque do Almoxarifado');
define('SITE_URL', 'http://seusite.com/gesp');

// Configurações da sessão
define('SESSION_TIMEOUT', 1800); // 30 minutos
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT); // 30 minutos
ini_set('session.cookie_lifetime', SESSION_TIMEOUT); // 30 minutos

// Iniciar sessão
session_start();

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

// Função para redirecionar para a página de login se não estiver logado
//function requireLogin() {
//    if (!isLoggedIn()) {
//        header('Location: ' . SITE_URL . '/login.php');
//        exit;
//    }
//}

// Função para verificar se o usuário está logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }

    // Verificar tempo de inatividade (30 minutos)
    if (isset($_SESSION['ultima_atividade']) && (time() - $_SESSION['ultima_atividade'] > 1800)) {
        // Destruir a sessão
        session_unset();
        session_destroy();
        
        // Redirecionar para o login com mensagem
        header('Location: ' . SITE_URL . '/login.php?erro=Sua sessão expirou por inatividade. Por favor, faça login novamente.');
        exit;
    }

    // Atualizar tempo da última atividade
    $_SESSION['ultima_atividade'] = time();
}


// Função para verificar se o usuário tem permissão para editar
function requireEditPermission() {
    if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] === 'Leitor') {
        header('Location: ' . SITE_URL . '/pages/produtos.php?erro=Você não tem permissão para realizar esta ação');
        exit;
    }
}

// Função para verificar se o usuário tem permissão para criar movimentações
function requireMovimentacaoPermission() {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['perfil'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
    
    if ($_SESSION['perfil'] === 'Leitor') {
        header('Location: ' . SITE_URL . '/pages/movimentacoes.php?erro=Você não tem permissão para criar movimentações');
        exit;
    }
}

// Função para verificar se o usuário é administrador
function requireAdminPermission() {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['perfil'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
    
    if ($_SESSION['perfil'] !== 'Administrador') {
        header('Location: ' . SITE_URL . '/pages/index.php?erro=Você não tem permissão para realizar esta operação');
        exit;
    }
} 
