<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    requireEditPermission();
    
    $codigo = trim($_POST['codigo']);
    $nome = trim($_POST['nome']);
    $quantidade = floatval($_POST['quantidade']);
    $quantidade_minima = floatval($_POST['quantidade_minima']);
    $unidade_id = intval($_POST['unidade_id']);
    $localizacao_id = intval($_POST['localizacao_id']);

    if (empty($codigo) || empty($nome) || $unidade_id <= 0 || $localizacao_id <= 0) {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        try {
            // Verificar se já existe um produto com este código
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE codigo = ?");
            $stmt->execute([$codigo]);
            if ($stmt->fetchColumn() > 0) {
                $erro = "Já existe um produto com este código.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO produtos (codigo, nome, quantidade, quantidade_minima, unidade_id, localizacao_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$codigo, $nome, $quantidade, $quantidade_minima, $unidade_id, $localizacao_id]);
                header('Location: /gesp/pages/produtos.php?mensagem=Produto cadastrado com sucesso!');
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar produto: " . $e->getMessage();
        }
    }
}

// Buscar unidades para o combo box
$stmt = $pdo->query("SELECT * FROM unidades ORDER BY nome");
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar localizações para o combo box
$stmt = $pdo->query("SELECT * FROM localizacoes ORDER BY nome");
$localizacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos
$stmt = $pdo->query("
    SELECT p.*, u.sigla as unidade_sigla, l.nome as localizacao_nome 
    FROM produtos p 
    LEFT JOIN unidades u ON p.unidade_id = u.unidade_id 
    LEFT JOIN localizacoes l ON p.localizacao_id = l.localizacao_id 
    ORDER BY p.codigo
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se o usuário tem permissão para editar
$pode_editar = isset($_SESSION['perfil']) && $_SESSION['perfil'] !== 'Leitor';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Produtos</h1>
            <?php if ($pode_editar): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal">
                Novo Produto
            </button>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['mensagem'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['mensagem']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['erro']); ?>
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
                                <th>Código</th>
                                <th>Nome</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Localização</th>
                                <?php if ($pode_editar): ?>
                                <th>Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $produto['quantidade'] <= $produto['quantidade_minima'] ? 'danger' : 'success'; ?>">
                                        <?php echo number_format($produto['quantidade'], 2, ',', '.'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($produto['unidade_sigla']); ?></td>
                                <td><?php echo htmlspecialchars($produto['localizacao_nome']); ?></td>
                                <?php if ($pode_editar): ?>
                                <td>
                                    <a href="/gesp/pages/editar_produto.php?id=<?php echo $produto['produto_id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="/gesp/pages/excluir_produto.php?id=<?php echo $produto['produto_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
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

    <?php if ($pode_editar): ?>
    <!-- Modal de Cadastro -->
    <div class="modal fade" id="cadastroModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required>
                        </div>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" step="0.01" class="form-control" id="quantidade" name="quantidade" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantidade_minima" class="form-label">Quantidade Mínima</label>
                            <input type="number" step="0.01" class="form-control" id="quantidade_minima" name="quantidade_minima" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="unidade_id" class="form-label">Unidade</label>
                            <select class="form-select" id="unidade_id" name="unidade_id" required>
                                <option value="">Selecione uma unidade</option>
                                <?php foreach ($unidades as $unidade): ?>
                                    <option value="<?php echo $unidade['unidade_id']; ?>">
                                        <?php echo htmlspecialchars($unidade['nome']); ?> (<?php echo htmlspecialchars($unidade['sigla']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="localizacao_id" class="form-label">Localização</label>
                            <select class="form-select" id="localizacao_id" name="localizacao_id" required>
                                <option value="">Selecione uma localização</option>
                                <?php foreach ($localizacoes as $localizacao): ?>
                                    <option value="<?php echo $localizacao['localizacao_id']; ?>">
                                        <?php echo htmlspecialchars($localizacao['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="cadastrar" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
