<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    if (empty($nome)) {
        $erro = "O nome da localização é obrigatório.";
    } else {
        try {
            // Verificar se já existe uma localização com este nome
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM localizacoes WHERE nome = ?");
            $stmt->execute([$nome]);
            if ($stmt->fetchColumn() > 0) {
                $erro = "Já existe uma localização com este nome.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO localizacoes (nome, descricao) VALUES (?, ?)");
                $stmt->execute([$nome, $descricao]);
                header('Location: /gesp/pages/localizacoes.php?mensagem=Localização cadastrada com sucesso!');
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar localização: " . $e->getMessage();
        }
    }
}

// Buscar localizações
$stmt = $pdo->query("SELECT * FROM localizacoes ORDER BY nome");
$localizacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localizações - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Localizações</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cadastroModal">
                Nova Localização
            </button>
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
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($localizacoes as $localizacao): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($localizacao['nome']); ?></td>
                                <td><?php echo htmlspecialchars($localizacao['descricao']); ?></td>
                                <td>
                                    <a href="/gesp/pages/editar_localizacao.php?id=<?php echo $localizacao['localizacao_id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="/gesp/pages/excluir_localizacao.php?id=<?php echo $localizacao['localizacao_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta localização?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro -->
    <div class="modal fade" id="cadastroModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Localização</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 