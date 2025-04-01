<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
// Determinar o caminho correto para o index.php baseado na profundidade do diretório
$current_path = $_SERVER['PHP_SELF'];
$depth = substr_count($current_path, '/') - 2; // -2 para ajustar o caminho base
//echo $depth;
$home_path = '/gesp/index.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/gesp/index.php"><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/gesp/index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/gesp/pages/produtos.php">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/gesp/pages/movimentacoes.php">Movimentações</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/gesp/pages/relatorios.php">Relatórios</a>
                </li>
                <?php if (isAdmin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Administração
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="/gesp/pages/unidades.php">Unidades</a></li>
                        <li><a class="dropdown-item" href="/gesp/pages/localizacoes.php">Localizações</a></li>
                        <li><a class="dropdown-item" href="/gesp/pages/usuarios.php">Usuários</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/gesp/pages/backup.php">Backup</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/gesp/logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav> 