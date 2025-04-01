<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/localizacoes.php?erro=ID não fornecido');
    exit;
}

$id = $_GET['id'];

// Buscar dados da localização
try {
    $stmt = $pdo->prepare("SELECT * FROM localizacoes WHERE localizacao_id = ?");
    $stmt->execute([$id]);
    $localizacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$localizacao) {
        header('Location: /gesp/pages/localizacoes.php?erro=Localização não encontrada');
        exit;
    }
} catch (PDOException $e) {
    header('Location: /gesp/pages/localizacoes.php?erro=Erro ao buscar localização');
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    if (empty($nome)) {
        $erro = "O nome da localização é obrigatório.";
    } else {
        try {
            // Verificar se já existe uma localização com este nome (exceto a atual)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM localizacoes WHERE nome = ? AND localizacao_id != ?");
            $stmt->execute([$nome, $id]);
            if ($stmt->fetchColumn() > 0) {
                $erro = "Já existe uma localização com este nome.";
            } else {
                $stmt = $pdo->prepare("UPDATE localizacoes SET nome = ?, descricao = ? WHERE localizacao_id = ?");
                $stmt->execute([$nome, $descricao, $id]);
                header('Location: /gesp/pages/localizacoes.php?mensagem=Localização atualizada com sucesso!');
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar localização: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Localização - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Editar Localização</h1>
            <a href="/gesp/pages/localizacoes.php" class="btn btn-secondary">Voltar</a>
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
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($localizacao['nome']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($localizacao['descricao']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 