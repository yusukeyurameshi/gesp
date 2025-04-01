<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
requireAdmin();

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validações
        $erros = [];
        
        if (empty($_POST['nome'])) {
            $erros[] = "O nome é obrigatório";
        } elseif (strlen($_POST['nome']) > 100) {
            $erros[] = "O nome não pode ter mais que 100 caracteres";
        }

        if (empty($_POST['sigla'])) {
            $erros[] = "A sigla é obrigatória";
        } elseif (strlen($_POST['sigla']) > 10) {
            $erros[] = "A sigla não pode ter mais que 10 caracteres";
        } else {
            // Verificar se a sigla já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM unidades WHERE sigla = ?");
            $stmt->execute([$_POST['sigla']]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $erros[] = "Esta sigla já está em uso";
            }
        }

        if (empty($erros)) {
            $stmt = $pdo->prepare("INSERT INTO unidades (nome, sigla) VALUES (?, ?)");
            $stmt->execute([$_POST['nome'], $_POST['sigla']]);
            
            header('Location: /gesp/pages/unidades.php?mensagem=Unidade cadastrada com sucesso!');
            exit;
        }
    } catch(PDOException $e) {
        $erros[] = "Erro ao cadastrar unidade: " . $e->getMessage();
    }
}

// Buscar unidades
$stmt = $pdo->query("SELECT * FROM unidades ORDER BY nome");
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unidades - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Unidades</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUnidade">
                Nova Unidade
            </button>
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

        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($erros as $erro): ?>
                        <li><?php echo $erro; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Sigla</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unidades as $unidade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($unidade['nome']); ?></td>
                                <td><?php echo htmlspecialchars($unidade['sigla']); ?></td>
                                <td>
                                    <a href="/gesp/pages/editar_unidade.php?id=<?php echo $unidade['unidade_id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="/gesp/pages/excluir_unidade.php?id=<?php echo $unidade['unidade_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta unidade?')">Excluir</a>
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
    <div class="modal fade" id="modalUnidade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Unidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="sigla" class="form-label">Sigla</label>
                            <input type="text" class="form-control" id="sigla" name="sigla" required maxlength="10">
                        </div>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 