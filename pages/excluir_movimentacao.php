<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
requireAdminPermission();

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: ' . SITE_URL . '/pages/movimentacoes.php?erro=ID da movimentação não fornecido');
    exit;
}

$movimentacao_id = intval($_GET['id']);

try {
    // Iniciar transação
    $pdo->beginTransaction();

    // Buscar dados da movimentação
    $stmt = $pdo->prepare("
        SELECT m.*, p.quantidade as produto_quantidade
        FROM movimentacoes m 
        JOIN produtos p ON m.produto_id = p.produto_id 
        WHERE m.movimentacao_id = ?
    ");
    $stmt->execute([$movimentacao_id]);
    $movimentacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$movimentacao) {
        throw new Exception("Movimentação não encontrada.");
    }

    // Reverter a movimentação no estoque
    $nova_quantidade = $movimentacao['produto_quantidade'];
    if ($movimentacao['tipo'] === 'entrada') {
        $nova_quantidade -= $movimentacao['quantidade'];
    } else {
        $nova_quantidade += $movimentacao['quantidade'];
    }

    // Atualizar quantidade do produto
    $stmt = $pdo->prepare("UPDATE produtos SET quantidade = ? WHERE produto_id = ?");
    $stmt->execute([$nova_quantidade, $movimentacao['produto_id']]);

    // Excluir movimentação
    $stmt = $pdo->prepare("DELETE FROM movimentacoes WHERE movimentacao_id = ?");
    $stmt->execute([$movimentacao_id]);

    // Confirmar transação
    $pdo->commit();

    header('Location: ' . SITE_URL . '/pages/movimentacoes.php?mensagem=Movimentação excluída com sucesso!');
    exit;
} catch (Exception $e) {
    // Reverter transação em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ' . SITE_URL . '/pages/movimentacoes.php?erro=' . urlencode($e->getMessage()));
    exit;
} 