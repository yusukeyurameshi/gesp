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
    $stmt = $pdo->prepare("SELECT cargo FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || $usuario['cargo'] !== 'Administrador') {
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
    $stmt = $pdo->prepare("SELECT cargo FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario && $usuario['cargo'] === 'Administrador';
} 