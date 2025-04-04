<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
requireEditPermission();

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/produtos.php?erro=ID do produto não fornecido');
    exit;
}

$id = $_GET['id'];

try {
    // Verificar se o produto existe
    $stmt = $pdo->prepare("SELECT nome FROM produtos WHERE produto_id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        header('Location: /gesp/pages/produtos.php?erro=Produto não encontrado');
        exit;
    }

    // Verificar se existem movimentações para este produto
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM movimentacoes WHERE produto_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['total'] > 0) {
        header('Location: /gesp/pages/produtos.php?erro=Não é possível excluir o produto "' . htmlspecialchars($produto['nome']) . '" pois existem movimentações registradas.');
        exit;
    }

    // Excluir o produto
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE produto_id = ?");
    $stmt->execute([$id]);

    header('Location: /gesp/pages/produtos.php?mensagem=Produto "' . htmlspecialchars($produto['nome']) . '" excluído com sucesso!');
} catch(PDOException $e) {
    header('Location: /gesp/pages/produtos.php?erro=Erro ao excluir produto: ' . urlencode($e->getMessage()));
}
exit; 