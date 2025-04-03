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

// Buscar dados da movimentação
$stmt = $pdo->prepare("
    SELECT m.*, p.codigo as produto_codigo, p.nome as produto_nome, p.quantidade as produto_quantidade,
           u.sigla as unidade_sigla, l.nome as localizacao_nome
    FROM movimentacoes m 
    JOIN produtos p ON m.produto_id = p.produto_id 
    LEFT JOIN unidades u ON p.unidade_id = u.unidade_id 
    LEFT JOIN localizacoes l ON p.localizacao_id = l.localizacao_id 
    WHERE m.movimentacao_id = ?
");
$stmt->execute([$movimentacao_id]);
$movimentacao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movimentacao) {
    header('Location: ' . SITE_URL . '/pages/movimentacoes.php?erro=Movimentação não encontrada');
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidade = floatval($_POST['quantidade']);
    $tipo = $_POST['tipo'];
    $observacao = trim($_POST['observacao']);

    if ($quantidade <= 0 || empty($observacao)) {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        try {
            // Iniciar transação
            $pdo->beginTransaction();

            try {
                // Reverter a movimentação anterior
                $quantidade_anterior = $movimentacao['quantidade'];
                $tipo_anterior = $movimentacao['tipo'];
                
                // Atualizar quantidade do produto
                $nova_quantidade = $movimentacao['produto_quantidade'];
                if ($tipo_anterior === 'entrada') {
                    $nova_quantidade -= $quantidade_anterior;
                } else {
                    $nova_quantidade += $quantidade_anterior;
                }

                // Aplicar nova movimentação
                if ($tipo === 'entrada') {
                    $nova_quantidade += $quantidade;
                } else {
                    if ($nova_quantidade < $quantidade) {
                        throw new Exception("Estoque insuficiente para esta movimentação.");
                    }
                    $nova_quantidade -= $quantidade;
                }

                // Atualizar produto
                $stmt = $pdo->prepare("UPDATE produtos SET quantidade = ? WHERE produto_id = ?");
                $stmt->execute([$nova_quantidade, $movimentacao['produto_id']]);

                // Atualizar movimentação
                $stmt = $pdo->prepare("UPDATE movimentacoes SET quantidade = ?, tipo = ?, observacao = ? WHERE movimentacao_id = ?");
                $stmt->execute([$quantidade, $tipo, $observacao, $movimentacao_id]);

                // Confirmar transação
                $pdo->commit();

                header('Location: ' . SITE_URL . '/pages/movimentacoes.php?mensagem=Movimentação atualizada com sucesso!');
                exit;
            } catch (Exception $e) {
                // Reverter transação em caso de erro
                $pdo->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $erro = "Erro ao atualizar movimentação: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Movimentação - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Editar Movimentação</h1>
            <a href="<?php echo SITE_URL; ?>/pages/movimentacoes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($erro); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Produto</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($movimentacao['produto_codigo'] . ' - ' . $movimentacao['produto_nome']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Localização</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($movimentacao['localizacao_nome']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" step="0.01" class="form-control" id="quantidade" name="quantidade" value="<?php echo $movimentacao['quantidade']; ?>" required>
                        <div class="form-text">Unidade: <?php echo htmlspecialchars($movimentacao['unidade_sigla']); ?></div>
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="entrada" <?php echo $movimentacao['tipo'] === 'entrada' ? 'selected' : ''; ?>>Entrada</option>
                            <option value="saida" <?php echo $movimentacao['tipo'] === 'saida' ? 'selected' : ''; ?>>Saída</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observacao" class="form-label">Observação</label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="3" required><?php echo htmlspecialchars($movimentacao['observacao']); ?></textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 