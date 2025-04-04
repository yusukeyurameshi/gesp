<?php
require_once 'functions.php';
?>
<nav class="menu">
    <ul>
        <li><a href="/gesp/pages/index.php">Início</a></li>
        
        <?php if (isColaborador()): ?>
            <li><a href="/gesp/pages/projetos.php">Projetos</a></li>
            <li><a href="/gesp/pages/tarefas.php">Tarefas</a></li>
            <li><a href="/gesp/pages/relatorios.php">Relatórios</a></li>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
            <li><a href="/gesp/pages/usuarios.php">Usuários</a></li>
            <li><a href="/gesp/pages/configuracoes.php">Configurações</a></li>
        <?php endif; ?>
        
        <li class="user-info">
            <span>Olá, <?php echo $_SESSION['nome']; ?> (<?php echo $_SESSION['perfil']; ?>)</span>
            <a href="/gesp/logout.php">Sair</a>
        </li>
    </ul>
</nav> 