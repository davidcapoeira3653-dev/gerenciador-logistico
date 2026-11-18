<?php
require_once __DIR__ . '/includes/funcoes.php';

/* ---------- Processa o formulário (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'excluir') {
        excluir('fretes', $_POST['id'] ?? '');
        flash('success', 'Frete excluído.');
        redirecionar('fretes.php');
    }

    if ($acao === 'salvar') {
        $data          = $_POST['data'] ?? '';
        $transportador = $_POST['transportador_id'] ?? '';
        $origem        = $_POST['origem_id'] ?? '';
        $destino       = $_POST['destino_id'] ?? '';
        $erros         = [];

        // Junta as linhas dinâmicas de produto (arrays produto_id[] e quantidade[])
        $itens = [];
        foreach (($_POST['produto_id'] ?? []) as $i => $produtoId) {
            $qtd = (int) ($_POST['quantidade'][$i] ?? 0);
            if ($produtoId !== '' && $qtd > 0 && buscar('produtos', $produtoId)) {
                $itens[] = ['produto_id' => (int) $produtoId, 'quantidade' => $qtd];
            }
        }

        if (!DateTime::createFromFormat('Y-m-d', $data)) {
            $erros[] = 'Informe a data do frete.';
        }
        if (!buscar('transportadores', $transportador)) {
            $erros[] = 'Selecione o transportador.';
        }
        if (!buscar('enderecos', $origem) || !buscar('enderecos', $destino)) {
            $erros[] = 'Selecione o endereço de origem e o de destino.';
        } elseif ($origem === $destino) {
            $erros[] = 'Origem e destino não podem ser o mesmo endereço.';
        }
        if (!$itens) {
            $erros[] = 'Adicione pelo menos um produto com quantidade maior que zero.';
        }

        if ($erros) {
            flash('danger', implode(' ', $erros));
        } else {
            salvar('fretes', [
                'id'               => null,
                'data'             => $data,
                'transportador_id' => (int) $transportador,
                'origem_id'        => (int) $origem,
                'destino_id'       => (int) $destino,
                'itens'            => $itens,      // 1 transportador -> vários produtos
                'observacao'       => trim($_POST['observacao'] ?? ''),
                'criado_em'        => date('Y-m-d H:i:s'),
            ]);
            flash('success', 'Frete gerado com sucesso!');
        }
        redirecionar('fretes.php');
    }
}

/* ---------- Exibe a página ---------- */
$produtos        = ler('produtos');
$enderecos       = ler('enderecos');
$transportadores = ler('transportadores');
$fretes          = array_reverse(ler('fretes'));   // mais recentes primeiro
$faltaCadastro   = !$produtos || count($enderecos) < 2 || !$transportadores;

$tituloPagina = 'Fretes';
require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-4">Fretes</h1>

<?php if ($faltaCadastro): ?>
    <div class="alert alert-warning">
        Antes de gerar um frete, cadastre pelo menos <a href="produtos.php">1 produto</a>,
        <a href="enderecos.php">2 endereços</a> e <a href="transportadores.php">1 transportador</a>.
    </div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-header">Gerar novo frete</div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="acao" value="salvar">

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="data" class="form-label">Data <span class="text-danger">*</span></label>
                        <input type="date" id="data" name="data" class="form-control"
                               value="<?= e(date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-md-9">
                        <label for="transportador_id" class="form-label">Transportador <span class="text-danger">*</span></label>
                        <select id="transportador_id" name="transportador_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($transportadores as $t): ?>
                                <option value="<?= e($t['id']) ?>"><?= e($t['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="origem_id" class="form-label">Endereço de origem <span class="text-danger">*</span></label>
                        <select id="origem_id" name="origem_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($enderecos as $end): ?>
                                <option value="<?= e($end['id']) ?>"><?= e(textoEndereco($end)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="destino_id" class="form-label">Endereço de destino <span class="text-danger">*</span></label>
                        <select id="destino_id" name="destino_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($enderecos as $end): ?>
                                <option value="<?= e($end['id']) ?>"><?= e(textoEndereco($end)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h2 class="h6">Produtos transportados <span class="text-danger">*</span></h2>
                <div id="lista-itens"></div>
                <button type="button" id="btn-adicionar-item" class="btn btn-sm btn-outline-secondary mb-3">
                    + Adicionar produto
                </button>

                <div class="mb-3">
                    <label for="observacao" class="form-label">Observação</label>
                    <input type="text" id="observacao" name="observacao" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Gerar frete</button>
            </form>
        </div>
    </div>

    <!-- Modelo de linha copiado pelo JavaScript (assets/js/app.js) -->
    <template id="modelo-item">
        <div class="row g-2 mb-2 item-frete">
            <div class="col-8 col-md-9">
                <select name="produto_id[]" class="form-select" aria-label="Produto">
                    <option value="">Selecione o produto...</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= e($p['id']) ?>"><?= e($p['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-md-2">
                <input type="number" name="quantidade[]" class="form-control" min="1" value="1" aria-label="Quantidade">
            </div>
            <div class="col-12 col-md-1 d-grid">
                <button type="button" class="btn btn-outline-danger btn-remover-item" title="Remover">&times;</button>
            </div>
        </div>
    </template>
<?php endif; ?>

<div class="card">
    <div class="card-header">Fretes gerados (<?= count($fretes) ?>)</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead>
            <tr>
                <th>#</th><th>Data</th><th>Transportador</th><th>Rota</th><th>Produtos</th><th class="text-end">Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$fretes): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum frete gerado ainda.</td></tr>
            <?php endif; ?>

            <?php foreach ($fretes as $f):
                $transp = buscar('transportadores', $f['transportador_id']);
                ?>
                <tr>
                    <td><?= e($f['id']) ?></td>
                    <td><?= e(dataBR($f['data'])) ?></td>
                    <td><?= e($transp['nome'] ?? '(removido)') ?></td>
                    <td>
                        <small>
                            <strong>De:</strong> <?= e(textoEndereco(buscar('enderecos', $f['origem_id']))) ?><br>
                            <strong>Para:</strong> <?= e(textoEndereco(buscar('enderecos', $f['destino_id']))) ?>
                        </small>
                    </td>
                    <td>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($f['itens'] as $item):
                                $prod = buscar('produtos', $item['produto_id']); ?>
                                <li><?= e($item['quantidade']) ?> x <?= e($prod['nome'] ?? '(removido)') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td class="text-end">
                        <form method="post" class="d-inline"
                              onsubmit="return confirm('Deseja realmente excluir este frete?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= e($f['id']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
