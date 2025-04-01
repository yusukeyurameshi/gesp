<?php
require_once '../includes/config.php';
requireLogin();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    // Verificar se o diretório de backups existe
                    if (!file_exists('../backups')) {
                        mkdir('../backups', 0775, true);
                    }

                    // Gerar nome do arquivo com timestamp
                    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                    $filepath = '../backups/' . $filename;

                    // Abrir arquivo para escrita
                    $file = fopen($filepath, 'w');
                    if (!$file) {
                        throw new Exception('Não foi possível criar o arquivo de backup');
                    }

                    // Obter todas as tabelas
                    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

                    // Para cada tabela
                    foreach ($tables as $table) {
                        // Obter estrutura da tabela
                        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
                        fwrite($file, "\nDROP TABLE IF EXISTS `$table`;\n");
                        fwrite($file, $create[1] . ";\n\n");

                        // Obter dados da tabela
                        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($rows as $row) {
                            $values = array_map(function($value) use ($pdo) {
                                if ($value === null) return 'NULL';
                                return $pdo->quote($value);
                            }, $row);

                            fwrite($file, "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n");
                        }
                        fwrite($file, "\n");
                    }

                    fclose($file);
                    $_SESSION['success'] = 'Backup criado com sucesso!';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Erro ao criar backup: ' . $e->getMessage();
                }
                break;

            case 'restore':
                if (isset($_POST['backup_file']) && file_exists('../backups/' . $_POST['backup_file'])) {
                    try {
                        // Ler arquivo de backup
                        $sql = file_get_contents('../backups/' . $_POST['backup_file']);
                        
                        // Executar comandos SQL
                        $pdo->exec($sql);
                        
                        $_SESSION['success'] = 'Backup restaurado com sucesso!';
                    } catch (Exception $e) {
                        $_SESSION['error'] = 'Erro ao restaurar backup: ' . $e->getMessage();
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['backup_file']) && file_exists('../backups/' . $_POST['backup_file'])) {
                    try {
                        unlink('../backups/' . $_POST['backup_file']);
                        $_SESSION['success'] = 'Backup excluído com sucesso!';
                    } catch (Exception $e) {
                        $_SESSION['error'] = 'Erro ao excluir backup: ' . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Listar backups existentes
$backups = [];
if (file_exists('../backups')) {
    $files = glob('../backups/*.sql');
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
}
usort($backups, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup do Sistema - GESP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2>Backup do Sistema</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row mt-4">
            <!--<div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Criar Backup</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="create">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-download"></i> Criar Novo Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>-->

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Backups Existentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($backups)): ?>
                            <p class="text-muted">Nenhum backup encontrado.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Tamanho</th>
                                            <!--<th>Ações</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backups as $backup): ?>
                                            <tr>
                                                <td><?php echo $backup['date']; ?></td>
                                                <td><?php echo number_format($backup['size'] / 1024, 2) . ' KB'; ?></td>
                                                <!--<td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="restore">
                                                        <input type="hidden" name="backup_file" value="<?php echo $backup['name']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Tem certeza que deseja restaurar este backup?')">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    </form>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="backup_file" value="<?php echo $backup['name']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este backup?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>-->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 