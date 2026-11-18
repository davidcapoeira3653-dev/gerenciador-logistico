<?php
require_once __DIR__ . '/includes/funcoes.php';

$tituloPagina = 'Início';
require __DIR__ . '/includes/header.php';

$cards = [
    ['produtos.php',        'Produtos',        count(ler('produtos'))],
    ['enderecos.php',       'Endereços',       count(ler('enderecos'))],
    ['transportadores.php', 'Transportadores', count(ler('transportadores'))],
    ['fretes.php',          'Fretes',          count(ler('fretes'))],
];
?>
<div class="p-4 mb-4 bg-white rounded shadow-sm">
    <h1 class="h3">Gerenciador Logístico</h1>
    <p class="mb-0 text-muted">
        Cadastre produtos, endereços e transportadores; depois gere um frete ligando esses dados
        e baixe o relatório de fretes de um dia.
    </p>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($cards as [$link, $nome, $total]): ?>
        <div class="col-6 col-lg-3">
            <a href="<?= e($link) ?>" class="text-decoration-none">
                <div class="card card-resumo h-100 text-center">
                    <div class="card-body">
                        <div class="numero-grande text-primary"><?= (int) $total ?></div>
                        <div class="text-dark"><?= e($nome) ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">Como usar</div>
    <div class="card-body">
        <ol class="mb-0">
            <li>Cadastre ao menos <strong>1 produto</strong>, <strong>2 endereços</strong> (origem e destino) e <strong>1 transportador</strong>.</li>
            <li>Vá em <strong>Fretes</strong>, escolha os dados e adicione quantos produtos quiser.</li>
            <li>Em <strong>Relatório</strong>, escolha o dia e baixe o arquivo CSV.</li>
        </ol>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
