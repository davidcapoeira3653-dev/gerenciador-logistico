<?php
require_once __DIR__ . '/includes/funcoes.php';

// Data escolhida (padrão: hoje). Valida o formato antes de usar.
$data = $_GET['data'] ?? date('Y-m-d');
if (!DateTime::createFromFormat('Y-m-d', $data)) {
    $data = date('Y-m-d');
}

$linhas = linhasRelatorio($data);

/* ---------- Download em CSV ---------- */
if (($_GET['formato'] ?? '') === 'csv') {
    // Cabeçalhos HTTP: Content-Type (MIME type) e Content-Disposition (forçar download)
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="relatorio-fretes-' . $data . '.csv"');

    $saida = fopen('php://output', 'w');
    fwrite($saida, "\xEF\xBB\xBF"); // BOM para o Excel reconhecer acentos
    fputcsv($saida, ['Frete', 'Data', 'Transportador', 'Origem', 'Destino', 'Produto', 'Quantidade',
                     'Peso total (kg)', 'Valor total (R$)'], ';');

    foreach ($linhas as $l) {
        fputcsv($saida, [
            $l['frete'], dataBR($l['data']), $l['transportador'], $l['origem'], $l['destino'],
            $l['produto'], $l['quantidade'],
            number_format($l['peso_total'], 2, ',', ''),
            number_format($l['valor_total'], 2, ',', ''),
        ], ';');
    }
    fclose($saida);
    exit;
}

/* ---------- Exibe na tela ---------- */
$totalPeso  = array_sum(array_column($linhas, 'peso_total'));
$totalValor = array_sum(array_column($linhas, 'valor_total'));
$qtdFretes  = count(array_unique(array_column($linhas, 'frete')));

$tituloPagina = 'Relatório';
require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-4">Relatório de fretes do dia</h1>

<form method="get" class="card card-body mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="data" class="form-label">Data</label>
            <input type="date" id="data" name="data" class="form-control" value="<?= e($data) ?>" required>
        </div>
        <div class="col-md-8">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <?php if ($linhas): ?>
                <a href="?data=<?= e($data) ?>&amp;formato=csv" class="btn btn-success">&#11015; Baixar CSV</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header">
        Fretes de <?= e(dataBR($data)) ?> &mdash; <?= (int) $qtdFretes ?> frete(s)
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead>
            <tr>
                <th>Frete</th><th>Transportador</th><th>Origem</th><th>Destino</th>
                <th>Produto</th><th>Qtd</th><th>Peso (kg)</th><th>Valor</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$linhas): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Nenhum frete nesta data.</td></tr>
            <?php endif; ?>
            <?php foreach ($linhas as $l): ?>
                <tr>
                    <td>#<?= e($l['frete']) ?></td>
                    <td><?= e($l['transportador']) ?></td>
                    <td><small><?= e($l['origem']) ?></small></td>
                    <td><small><?= e($l['destino']) ?></small></td>
                    <td><?= e($l['produto']) ?></td>
                    <td><?= e($l['quantidade']) ?></td>
                    <td><?= e(number_format($l['peso_total'], 2, ',', '.')) ?></td>
                    <td><?= e(dinheiro($l['valor_total'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <?php if ($linhas): ?>
                <tfoot>
                <tr class="fw-bold">
                    <td colspan="6" class="text-end">Totais:</td>
                    <td><?= e(number_format($totalPeso, 2, ',', '.')) ?></td>
                    <td><?= e(dinheiro($totalValor)) ?></td>
                </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
