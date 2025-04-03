<?php
// Função para verificar se o usuário está logado
//function requireLogin() {

// Função para verificar se o usuário é administrador
function requireAdmin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /gesp/login.php');
        exit;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT perfil FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || $usuario['perfil'] !== 'Administrador') {
        header('Location: /gesp/pages/index.php?erro=Acesso não autorizado');
        exit;
    }
}

// Função para verificar se o usuário é administrador (retorna true/false)
function isAdmin() {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT perfil FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario && $usuario['perfil'] === 'Administrador';
}

// Função para verificar se o usuário é colaborador (retorna true/false)
function isColaborador() {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT perfil FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario && ($usuario['perfil'] === 'Administrador' || $usuario['perfil'] === 'Colaborador');
}

// Função para verificar se o usuário é leitor (retorna true/false)
function isLeitor() {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT perfil FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario && ($usuario['perfil'] === 'Administrador' || $usuario['perfil'] === 'Colaborador' || $usuario['perfil'] === 'Leitor');
}

// Função para verificar se o usuário tem permissão para acessar uma página
function requirePermission($perfil) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /gesp/login.php');
        exit;
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT perfil FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: /gesp/login.php');
        exit;
    }

    $temPermissao = false;
    switch ($perfil) {
        case 'Administrador':
            $temPermissao = ($usuario['perfil'] === 'Administrador');
            break;
        case 'Colaborador':
            $temPermissao = ($usuario['perfil'] === 'Administrador' || $usuario['perfil'] === 'Colaborador');
            break;
        case 'Leitor':
            $temPermissao = ($usuario['perfil'] === 'Administrador' || $usuario['perfil'] === 'Colaborador' || $usuario['perfil'] === 'Leitor');
            break;
    }

    if (!$temPermissao) {
        header('Location: /gesp/pages/index.php?erro=Acesso não autorizado');
        exit;
    }
} 