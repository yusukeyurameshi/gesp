<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
requireEditPermission();

if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/produtos.php?erro=ID não fornecido');
    exit;
}

$id = $_GET['id'];

// Buscar unidades para o combo box
$stmt = $pdo->query("SELECT * FROM unidades ORDER BY nome");
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar localizações para o combo box
$stmt = $pdo->query("SELECT * FROM localizacoes ORDER BY nome");
$localizacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar dados do produto
try {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE produto_id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        header('Location: /gesp/pages/produtos.php?erro=Produto não encontrado');
        exit;
    }
} catch (PDOException $e) {
    header('Location: /gesp/pages/produtos.php?erro=Erro ao buscar produto');
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Verificar se já existe um produto com este código (exceto o atual)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE codigo = ? AND produto_id != ?");
            $stmt->execute([$codigo, $id]);
            if ($stmt->fetchColumn() > 0) {
                $erro = "Já existe um produto com este código.";
            } else {
                $stmt = $pdo->prepare("UPDATE produtos SET codigo = ?, nome = ?, quantidade = ?, quantidade_minima = ?, unidade_id = ?, localizacao_id = ? WHERE produto_id = ?");
                $stmt->execute([$codigo, $nome, $quantidade, $quantidade_minima, $unidade_id, $localizacao_id, $id]);
                header('Location: /gesp/pages/produtos.php?mensagem=Produto atualizado com sucesso!');
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar produto: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Editar Produto</h1>
            <a href="/gesp/pages/produtos.php" class="btn btn-secondary">Voltar</a>
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
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo htmlspecialchars($produto['codigo']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" step="0.01" class="form-control" id="quantidade" name="quantidade" value="<?php echo htmlspecialchars($produto['quantidade']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade_minima" class="form-label">Quantidade Mínima</label>
                        <input type="number" step="0.01" class="form-control" id="quantidade_minima" name="quantidade_minima" value="<?php echo htmlspecialchars($produto['quantidade_minima']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="unidade_id" class="form-label">Unidade</label>
                        <select class="form-select" id="unidade_id" name="unidade_id" required>
                            <option value="">Selecione uma unidade</option>
                            <?php foreach ($unidades as $unidade): ?>
                                <option value="<?php echo $unidade['unidade_id']; ?>" <?php echo $unidade['unidade_id'] == $produto['unidade_id'] ? 'selected' : ''; ?>>
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
                                <option value="<?php echo $localizacao['localizacao_id']; ?>" <?php echo $localizacao['localizacao_id'] == $produto['localizacao_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($localizacao['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 