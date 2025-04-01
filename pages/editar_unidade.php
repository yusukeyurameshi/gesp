<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
requireAdmin();

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/unidades.php');
    exit;
}

$id = $_GET['id'];

// Buscar dados da unidade
try {
    $stmt = $pdo->prepare("SELECT * FROM unidades WHERE unidade_id = ?");
    $stmt->execute([$id]);
    $unidade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$unidade) {
        header('Location: /gesp/pages/unidades.php?erro=Unidade não encontrada');
        exit;
    }

    // Buscar produtos que usam esta unidade
    $stmt = $pdo->prepare("
        SELECT p.codigo, p.nome, p.quantidade 
        FROM produtos p 
        WHERE p.unidade_id = ? 
        ORDER BY p.nome
    ");
    $stmt->execute([$id]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    header('Location: /gesp/pages/unidades.php?erro=Erro ao buscar unidade: ' . urlencode($e->getMessage()));
    exit;
}

// Processar formulário de edição
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
            // Verificar se a sigla já existe (exceto para a própria unidade)
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM unidades WHERE sigla = ? AND unidade_id != ?");
            $stmt->execute([$_POST['sigla'], $id]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $erros[] = "Esta sigla já está em uso";
            }
        }

        if (empty($erros)) {
            $stmt = $pdo->prepare("UPDATE unidades SET nome = ?, sigla = ? WHERE unidade_id = ?");
            $stmt->execute([$_POST['nome'], $_POST['sigla'], $id]);
            header('Location: /gesp/pages/unidades.php?mensagem=Unidade atualizada com sucesso!');
            exit;
        } else {
            $erro = implode("<br>", $erros);
        }
    } catch(PDOException $e) {
        header('Location: /gesp/pages/unidades.php?erro=Erro ao atualizar unidade: ' . urlencode($e->getMessage()));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Unidade - Sistema de Almoxarifado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .table th {
            font-weight: 600;
        }
        .badge {
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Editar Unidade</h1>
                <p class="text-muted mb-0">ID: <?php echo $unidade['unidade_id']; ?></p>
            </div>
            <a href="/gesp/pages/unidades.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações da Unidade</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required maxlength="100" value="<?php echo htmlspecialchars($unidade['nome']); ?>">
                                <div class="invalid-feedback">
                                    Por favor, informe o nome da unidade.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="sigla" class="form-label">Sigla</label>
                                <input type="text" class="form-control" id="sigla" name="sigla" required maxlength="10" value="<?php echo htmlspecialchars($unidade['sigla']); ?>">
                                <div class="invalid-feedback">
                                    Por favor, informe a sigla da unidade.
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Salvar
                                </button>
                                <a href="/gesp/pages/unidades.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Produtos que usam esta unidade</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($produtos)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Nenhum produto está usando esta unidade.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Produto</th>
                                            <th>Estoque</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produtos as $produto): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($produto['codigo']); ?></td>
                                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $produto['quantidade'] <= 0 ? 'danger' : 'success'; ?>">
                                                    <?php echo number_format($produto['quantidade'], 2, ',', '.'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html> 