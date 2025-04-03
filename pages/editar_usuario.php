<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/usuarios.php?erro=ID do usuário não fornecido');
    exit;
}

$usuario_id = $_GET['id'];

try {
    // Buscar dados do usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: /gesp/pages/usuarios.php?erro=Usuário não encontrado');
        exit;
    }

    // Processar formulário de edição
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $erros = [];
        
        if (empty($_POST['nome'])) {
            $erros[] = "O nome é obrigatório";
        } elseif (strlen($_POST['nome']) > 100) {
            $erros[] = "O nome não pode ter mais que 100 caracteres";
        }

        if (empty($_POST['username'])) {
            $erros[] = "O usuário é obrigatório";
        } elseif (strlen($_POST['username']) > 50) {
            $erros[] = "O usuário não pode ter mais que 50 caracteres";
        } else {
            // Verificar se o usuário já existe para outro usuário
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE username = ? AND usuario_id != ?");
            $stmt->execute([$_POST['username'], $usuario_id]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $erros[] = "Este usuário já está em uso";
            }
        }

        if (empty($_POST['cargo'])) {
            $erros[] = "O cargo é obrigatório";
        } elseif (strlen($_POST['cargo']) > 100) {
            $erros[] = "O cargo não pode ter mais que 100 caracteres";
        }

        // Se uma nova senha foi fornecida, validar
        if (!empty($_POST['senha'])) {
            if (strlen($_POST['senha']) < 6) {
                $erros[] = "A senha deve ter no mínimo 6 caracteres";
            }
            if ($_POST['senha'] !== $_POST['confirmar_senha']) {
                $erros[] = "As senhas não conferem";
            }
        }

        if (empty($erros)) {
            // Preparar a query base
            $query = "UPDATE usuarios SET nome = ?, username = ?, cargo = ?";
            $params = [$_POST['nome'], $_POST['username'], $_POST['cargo']];

            // Se uma nova senha foi fornecida, adicionar à query
            if (!empty($_POST['senha'])) {
                $query .= ", senha = ?";
                $params[] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            }

            $query .= " WHERE usuario_id = ?";
            $params[] = $usuario_id;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            header('Location: /gesp/pages/usuarios.php?mensagem=Usuário atualizado com sucesso!');
            exit;
        }
    }
} catch(PDOException $e) {
    $erros[] = "Erro ao buscar dados do usuário: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Editar Usuário</h1>
            <a href="/gesp/pages/usuarios.php" class="btn btn-secondary">Voltar</a>
        </div>

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
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo</label>
                        <input type="text" class="form-control" id="cargo" name="cargo" value="<?php echo htmlspecialchars($usuario['cargo']); ?>" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" class="form-control" id="senha" name="senha" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 