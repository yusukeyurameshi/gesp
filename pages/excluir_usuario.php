<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
requireAdmin();

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/usuarios.php?erro=ID do usuário não fornecido');
    exit;
}

$usuario_id = $_GET['id'];

try {
    // Verificar se o usuário existe
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: /gesp/pages/usuarios.php?erro=Usuário não encontrado');
        exit;
    }

    // Não permitir excluir o próprio usuário
    if ($usuario_id == $_SESSION['usuario_id']) {
        header('Location: /gesp/pages/usuarios.php?erro=Não é possível excluir seu próprio usuário');
        exit;
    }

    // Excluir o usuário
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);

    header('Location: /gesp/pages/usuarios.php?mensagem=Usuário excluído com sucesso!');
} catch(PDOException $e) {
    header('Location: /gesp/pages/usuarios.php?erro=Erro ao excluir usuário: ' . urlencode($e->getMessage()));
} 