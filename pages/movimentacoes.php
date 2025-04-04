<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Buscar produtos e unidades para o combo box
$stmt = $pdo->query("SELECT p.*, u.nome as unidade_nome, u.sigla as unidade_sigla, l.nome as localizacao_nome FROM produtos p JOIN unidades u ON p.unidade_id = u.unidade_id LEFT JOIN localizacoes l ON p.localizacao_id = l.localizacao_id ORDER BY p.nome");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar formulário de movimentação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movimentar'])) {
    requireMovimentacaoPermission();
    
    $produto_id = intval($_POST['produto_id']);
    $quantidade = floatval($_POST['quantidade']);
    $tipo = $_POST['tipo'];
    $observacao = trim($_POST['observacao']);

    if ($produto_id <= 0 || $quantidade <= 0 || empty($observacao)) {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        try {
            // Buscar dados do produto
            $stmt = $pdo->prepare("SELECT quantidade FROM produtos WHERE produto_id = ?");
            $stmt->execute([$produto_id]);
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produto) {
                $erro = "Produto não encontrado.";
            } else {
                // Verificar se há estoque suficiente para saída
                if ($tipo === 'saida' && $produto['quantidade'] < $quantidade) {
                    $erro = "Estoque insuficiente para esta movimentação.";
                } else {
                    // Iniciar transação
                    $pdo->beginTransaction();

                    try {
                        // Atualizar quantidade do produto
                        $nova_quantidade = $tipo === 'entrada' ? $produto['quantidade'] + $quantidade : $produto['quantidade'] - $quantidade;
                        $stmt = $pdo->prepare("UPDATE produtos SET quantidade = ? WHERE produto_id = ?");
                        $stmt->execute([$nova_quantidade, $produto_id]);

                        // Registrar movimentação
                        $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, quantidade, tipo, data, observacao, usuario_id) VALUES (?, ?, ?, NOW(), ?, ?)");
                        $stmt->execute([$produto_id, $quantidade, $tipo, $observacao, $_SESSION['usuario_id']]);

                        // Confirmar transação
                        $pdo->commit();

                        header('Location: ' . SITE_URL . '/pages/movimentacoes.php?mensagem=Movimentação registrada com sucesso!');
                        exit;
                    } catch (PDOException $e) {
                        // Reverter transação em caso de erro
                        $pdo->rollBack();
                        throw $e;
                    }
                }
            }
        } catch (PDOException $e) {
            $erro = "Erro ao registrar movimentação: " . $e->getMessage();
        }
    }
}

// Buscar movimentações
$stmt = $pdo->query("
    SELECT m.*, p.codigo as produto_codigo, p.nome as produto_nome, u.sigla as unidade_sigla, l.nome as localizacao_nome, us.nome as usuario_nome 
    FROM movimentacoes m 
    JOIN produtos p ON m.produto_id = p.produto_id 
    LEFT JOIN unidades u ON p.unidade_id = u.unidade_id 
    LEFT JOIN localizacoes l ON p.localizacao_id = l.localizacao_id 
    JOIN usuarios us ON m.usuario_id = us.usuario_id 
    ORDER BY m.data DESC, m.movimentacao_id DESC
");
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se o usuário tem permissão para criar movimentações
$pode_movimentar = isset($_SESSION['perfil']) && $_SESSION['perfil'] !== 'Leitor';
$pode_editar = isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Movimentações</h1>
            <?php if ($pode_movimentar): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#movimentacaoModal">
                Nova Movimentação
            </button>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['mensagem']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($erro); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Produto</th>
                                <th>Localização</th>
                                <th>Quantidade</th>
                                <th>Tipo</th>
                                <th>Observação</th>
                                <th>Usuário</th>
                                <?php if ($pode_editar): ?>
                                <th>Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoes as $movimentacao): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($movimentacao['data'])); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['produto_codigo'] . ' - ' . $movimentacao['produto_nome']); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['localizacao_nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $movimentacao['tipo'] === 'entrada' ? 'success' : 'danger'; ?>">
                                        <?php echo number_format($movimentacao['quantidade'], 2, ',', '.'); ?> <?php echo htmlspecialchars($movimentacao['unidade_sigla']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $movimentacao['tipo'] === 'entrada' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($movimentacao['tipo']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($movimentacao['observacao']); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['usuario_nome']); ?></td>
                                <?php if ($pode_editar): ?>
                                <td>
                                    <a href="editar_movimentacao.php?id=<?php echo $movimentacao['movimentacao_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="excluir_movimentacao.php?id=<?php echo $movimentacao['movimentacao_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta movimentação?')">
                                        <i class="fas fa-trash"></i> Excluir
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if ($pode_movimentar): ?>
    <!-- Modal de Movimentação -->
    <div class="modal fade" id="movimentacaoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Movimentação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="produto_id" class="form-label">Produto</label>
                            <select class="form-select" id="produto_id" name="produto_id" required>
                                <option value="">Selecione um produto</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?php echo $produto['produto_id']; ?>">
                                        <?php echo htmlspecialchars($produto['codigo'] . ' - ' . $produto['nome'] . ' (' . $produto['localizacao_nome'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" step="0.01" class="form-control" id="quantidade" name="quantidade" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="entrada">Entrada</option>
                                <option value="saida">Saída</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="movimentar" class="btn btn-primary">Registrar Movimentação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 