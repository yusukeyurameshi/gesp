<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
//requireAdmin();

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/unidades.php');
    exit;
}

$id = $_GET['id'];

try {
    // Verificar se existem produtos usando esta unidade
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM produtos WHERE unidade_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
        header('Location: /gesp/pages/unidades.php?erro=Não é possível excluir esta unidade pois existem produtos cadastrados com ela.');
        exit;
    }

    // Excluir a unidade
    $stmt = $pdo->prepare("DELETE FROM unidades WHERE unidade_id = ?");
    $stmt->execute([$id]);

    header('Location: /gesp/pages/unidades.php?mensagem=Unidade excluída com sucesso!');
} catch(PDOException $e) {
    header('Location: /gesp/pages/unidades.php?erro=Erro ao excluir unidade: ' . urlencode($e->getMessage()));
}
exit; 