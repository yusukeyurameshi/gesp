<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Verificar se foi fornecido um ID
if (!isset($_GET['id'])) {
    header('Location: /gesp/pages/usuarios.php?erro=ID do usuário não fornecido');
    exit;
}

$id = $_GET['id'];

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario_id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: /gesp/pages/usuarios.php?erro=Usuário não encontrado');
    exit;
}

// Processar formulário
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
        // Verificar se o usuário já existe (exceto para o próprio usuário)
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE username = ? AND usuario_id != ?");
        $stmt->execute([$_POST['username'], $id]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            $erros[] = "Este usuário já está em uso";
        }
    }

    if (!empty($_POST['senha'])) {
        if (strlen($_POST['senha']) < 6) {
            $erros[] = "A senha deve ter no mínimo 6 caracteres";
        } elseif ($_POST['senha'] !== $_POST['confirmar_senha']) {
            $erros[] = "As senhas não conferem";
        }
    }

    if (empty($_POST['perfil'])) {
        $erros[] = "O perfil é obrigatório";
    } elseif (!in_array($_POST['perfil'], ['Administrador', 'Colaborador', 'Leitor'])) {
        $erros[] = "Perfil inválido";
    }

    if (empty($erros)) {
        try {
            // Preparar a query base
            $sql = "UPDATE usuarios SET nome = ?, username = ?, perfil = ?, ativo = ?";
            $params = [
                $_POST['nome'],
                $_POST['username'],
                $_POST['perfil'],
                isset($_POST['ativo']) ? 1 : 0
            ];

            // Se uma nova senha foi fornecida, incluí-la na atualização
            if (!empty($_POST['senha'])) {
                $sql .= ", senha = ?";
                $params[] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE usuario_id = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            header('Location: /gesp/pages/usuarios.php?mensagem=Usuário atualizado com sucesso!');
            exit;
        } catch (PDOException $e) {
            $erros[] = "Erro ao atualizar usuário: " . $e->getMessage();
        }
    }
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
                <form method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" class="form-control" id="senha" name="senha" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil</label>
                        <select class="form-select" id="perfil" name="perfil" required>
                            <option value="">Selecione um perfil</option>
                            <option value="Administrador" <?php echo $usuario['perfil'] === 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                            <option value="Colaborador" <?php echo $usuario['perfil'] === 'Colaborador' ? 'selected' : ''; ?>>Colaborador</option>
                            <option value="Leitor" <?php echo $usuario['perfil'] === 'Leitor' ? 'selected' : ''; ?>>Leitor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="ativo" name="ativo" <?php echo $usuario['ativo'] ? 'checked' : ''; ?> <?php echo $usuario['usuario_id'] == $_SESSION['usuario_id'] ? 'disabled' : ''; ?>>
                            <label class="form-check-label" for="ativo">Usuário Ativo</label>
                        </div>
                        <?php if ($usuario['usuario_id'] == $_SESSION['usuario_id']): ?>
                            <small class="text-muted">Você não pode desativar seu próprio usuário</small>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
</html> 