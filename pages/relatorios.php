<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Buscar produtos
$stmt = $pdo->query("
    SELECT p.*, u.sigla as unidade_sigla, l.nome as localizacao_nome, tp.nome as tipo_nome
    FROM produtos p 
    LEFT JOIN unidades u ON p.unidade_id = u.unidade_id 
    LEFT JOIN localizacoes l ON p.localizacao_id = l.localizacao_id
    LEFT JOIN tipos_produtos tp ON p.tipo_id = tp.tipo_id
    ORDER BY p.nome
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar movimentações dos últimos 30 dias
$stmt = $pdo->query("
    SELECT m.*, p.nome as produto_nome, 
           u.sigla as unidade_sigla, l.nome as localizacao_nome, us.nome as usuario_nome
    FROM movimentacoes m 
    JOIN produtos p ON m.produto_id = p.produto_id 
    LEFT JOIN unidades u ON p.unidade_id = u.unidade_id 
    LEFT JOIN localizacoes l ON p.localizacao_id = l.localizacao_id 
    JOIN usuarios us ON m.usuario_id = us.usuario_id 
    WHERE m.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY m.data DESC, m.movimentacao_id DESC
");
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totais dos últimos 30 dias
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_movimentacoes,
        SUM(CASE WHEN tipo = 'entrada' THEN 1 ELSE 0 END) as total_entradas,
        SUM(CASE WHEN tipo = 'saida' THEN 1 ELSE 0 END) as total_saidas
    FROM movimentacoes 
    WHERE data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
");
$totais = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular totais
$total_produtos = count($produtos);
$total_movimentacoes = count($movimentacoes);
$produtos_baixo_estoque = 0;

foreach ($produtos as $produto) {
    if ($produto['quantidade'] <= $produto['quantidade_minima']) {
        $produtos_baixo_estoque++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .unidade-cursiva {
            font-family: 'Dancing Script', cursive;
            font-size: 1.1em;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Relatórios</h1>
        </div>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['erro']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total de Produtos</h5>
                        <p class="card-text display-4"><?php echo $total_produtos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Produtos com Baixo Estoque</h5>
                        <p class="card-text display-4"><?php echo $produtos_baixo_estoque; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Movimentações (30 dias)</h5>
                        <p class="card-text display-4"><?php echo $total_movimentacoes; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Saldo (30 dias)</h5>
                        <p class="card-text">
                            <span class="text-success">+<?php echo $totais['total_entradas']; ?></span> /
                            <span class="text-danger">-<?php echo $totais['total_saidas']; ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Estoque Atual</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Localização</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td><?php echo htmlspecialchars($produto['tipo_nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $produto['quantidade'] <= $produto['quantidade_minima'] ? 'danger' : 'success'; ?>">
                                        <?php echo number_format($produto['quantidade'], 2, ',', '.'); ?>
                                    </span>
                                </td>
                                <td><span class="unidade-cursiva"><?php echo htmlspecialchars($produto['unidade_sigla']); ?></span></td>
                                <td><?php echo htmlspecialchars($produto['localizacao_nome']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Movimentações Recentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Localização</th>
                                <th>Observação</th>
                                <th>Usuário</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoes as $movimentacao): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($movimentacao['data'])); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['produto_nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $movimentacao['tipo'] === 'entrada' ? 'success' : 'danger'; ?>">
                                        <?php echo $movimentacao['tipo'] === 'entrada' ? 'Entrada' : 'Saída'; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($movimentacao['quantidade'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['unidade_sigla']); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['localizacao_nome']); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['observacao']); ?></td>
                                <td><?php echo htmlspecialchars($movimentacao['usuario_nome']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
