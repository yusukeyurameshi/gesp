<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/localizacoes.php?erro=ID não fornecido');
    exit;
}

$id = $_GET['id'];

try {
    // Verificar se existem produtos usando esta localização
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE localizacao_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: /gesp/pages/localizacoes.php?erro=Não é possível excluir esta localização pois existem produtos associados a ela');
        exit;
    }

    // Buscar nome da localização para a mensagem
    $stmt = $pdo->prepare("SELECT nome FROM localizacoes WHERE localizacao_id = ?");
    $stmt->execute([$id]);
    $localizacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$localizacao) {
        header('Location: /gesp/pages/localizacoes.php?erro=Localização não encontrada');
        exit;
    }

    // Excluir localização
    $stmt = $pdo->prepare("DELETE FROM localizacoes WHERE localizacao_id = ?");
    $stmt->execute([$id]);

    header('Location: /gesp/pages/localizacoes.php?mensagem=Localização excluída com sucesso!');
} catch (PDOException $e) {
    header('Location: /gesp/pages/localizacoes.php?erro=Erro ao excluir localização: ' . urlencode($e->getMessage()));
} 