<?php
// Cabeçalho comum: abre o HTML, carrega o Bootstrap e mostra o menu de navegação.
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
$menu = [
    'index.php'           => 'Início',
    'produtos.php'        => 'Produtos',
    'enderecos.php'       => 'Endereços',
    'transportadores.php' => 'Transportadores',
    'fretes.php'          => 'Fretes',
    'relatorio.php'       => 'Relatório',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina ?? 'Gerenciador Logístico') ?> | Gerenciador Logístico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">&#128230; Gerenciador Logístico</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($menu as $arquivo => $rotulo): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaAtual === $arquivo ? 'active' : '' ?>"
                           href="<?= e($arquivo) ?>"><?= e($rotulo) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= e($_SESSION['flash']['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= e($_SESSION['flash']['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
