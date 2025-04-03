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

        if (empty($_POST['username'])) {
            $erros[] = "O usuário é obrigatório";
        } elseif (strlen($_POST['username']) > 50) {
            $erros[] = "O usuário não pode ter mais que 50 caracteres";
        } else {
            // Verificar se o usuário já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE username = ?");
            $stmt->execute([$_POST['username']]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $erros[] = "Este usuário já está em uso";
            }
        }

        if (empty($_POST['cargo'])) {
            $erros[] = "O cargo é obrigatório";
        } elseif (strlen($_POST['cargo']) > 100) {
            $erros[] = "O cargo não pode ter mais que 100 caracteres";
        }

        if (empty($_POST['senha'])) {
            $erros[] = "A senha é obrigatória";
        } elseif (strlen($_POST['senha']) < 6) {
            $erros[] = "A senha deve ter no mínimo 6 caracteres";
        }

        if ($_POST['senha'] !== $_POST['confirmar_senha']) {
            $erros[] = "As senhas não conferem";
        }

        if (empty($erros)) {
            $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, username, senha, cargo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['nome'], $_POST['username'], $senha_hash, $_POST['cargo']]);
            
            header('Location: /gesp/pages/usuarios.php?mensagem=Usuário cadastrado com sucesso!');
            exit;
        }
    } catch(PDOException $e) {
        $erros[] = "Erro ao cadastrar usuário: " . $e->getMessage();
    }
}

// Buscar usuários
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nome");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Usuários</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                Novo Usuário
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
                                <th>Usuário</th>
                                <th>Cargo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['cargo']); ?></td>
                                <td>
                                    <a href="/gesp/pages/editar_usuario.php?id=<?php echo $usuario['usuario_id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="/gesp/pages/excluir_usuario.php?id=<?php echo $usuario['usuario_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
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
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="cargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required minlength="6">
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