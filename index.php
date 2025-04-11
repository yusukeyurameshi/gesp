<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

// Buscar produtos
$stmt = $pdo->query("
    SELECT p.*, u.sigla as unidade_sigla, l.nome as localizacao_nome, tp.nome as tipo_nome
    FROM produtos p 
    LEFT JOIN unidades u ON p.unidade_id = u.unidade_id 
    LEFT JOIN localizacoes l ON p.localizacao_id = l.localizacao_id
    LEFT JOIN tipos_produtos tp ON p.tipo_id = tp.tipo_id
    ORDER BY tp.nome, p.nome
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard</h1>
            <a href="/gesp/pages/produtos.php" class="btn btn-primary">Gerenciar Produtos</a>
        </div>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['erro']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Localização</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['tipo_nome']); ?></td>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $produto['quantidade'] <= $produto['quantidade_minima'] ? 'danger' : 'success'; ?>">
                                        <?php echo number_format($produto['quantidade'], 2, ',', '.'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($produto['unidade_sigla']); ?></td>
                                <td><?php echo htmlspecialchars($produto['localizacao_nome']); ?></td>
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