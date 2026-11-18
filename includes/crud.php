<?php
/**
 * CRUD genérico (cadastrar, listar, editar, excluir).
 * Cada página (produtos, enderecos, transportadores) só descreve seus campos
 * e chama processarCrud() + renderCrud().
 */

// 1) Trata o envio do formulário (POST). Deve rodar ANTES de imprimir HTML,
//    porque usa header() para redirecionar.
function processarCrud(array $cfg): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $ent = $cfg['entidade'];
    $acao = $_POST['acao'] ?? '';
    $voltar = basename($_SERVER['SCRIPT_NAME']);

    if ($acao === 'excluir') {
        $id = $_POST['id'] ?? '';
        if (emUso($ent, $id)) {
            flash('danger', 'Não é possível excluir: este registro já foi usado em um frete.');
        } else {
            excluir($ent, $id);
            flash('success', 'Registro excluído.');
        }
        redirecionar($voltar);
    }

    if ($acao === 'salvar') {
        $dados = [];
        $erros = [];

        foreach ($cfg['campos'] as $nome => $campo) {
            $valor = trim($_POST[$nome] ?? '');

            if (!empty($campo['obrigatorio']) && $valor === '') {
                $erros[] = "O campo \"{$campo['rotulo']}\" é obrigatório.";
            }
            if (($campo['tipo'] ?? '') === 'number' && $valor !== '' && (!is_numeric($valor) || $valor < 0)) {
                $erros[] = "O campo \"{$campo['rotulo']}\" deve ser um número maior ou igual a zero.";
            }
            $dados[$nome] = ($campo['tipo'] ?? '') === 'number' && $valor !== '' ? (float) $valor : $valor;
        }

        if ($erros) {
            flash('danger', implode(' ', $erros));
            redirecionar($voltar . (!empty($_POST['id']) ? '?editar=' . (int) $_POST['id'] : ''));
        }

        $dados['id'] = !empty($_POST['id']) ? (int) $_POST['id'] : null;
        salvar($ent, $dados);
        flash('success', 'Registro salvo com sucesso!');
        redirecionar($voltar);
    }
}

// 2) Desenha o formulário e a tabela de registros
function renderCrud(array $cfg): void
{
    $ent = $cfg['entidade'];
    $editando = isset($_GET['editar']) ? buscar($ent, $_GET['editar']) : null;
    $registros = ler($ent);
    ?>
    <h1 class="h3 mb-4"><?= e($cfg['titulo']) ?></h1>

    <div class="card mb-4">
        <div class="card-header">
            <?= $editando ? 'Editar registro #' . e($editando['id']) : 'Novo cadastro' ?>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" value="<?= e($editando['id'] ?? '') ?>">

                <div class="row g-3">
                    <?php foreach ($cfg['campos'] as $nome => $campo):
                        $valor = $editando[$nome] ?? '';
                        $tipo = $campo['tipo'] ?? 'text';
                        $col = $campo['col'] ?? 6;
                        $req = !empty($campo['obrigatorio']) ? 'required' : '';
                        ?>
                        <div class="col-md-<?= (int) $col ?>">
                            <label for="<?= e($nome) ?>" class="form-label">
                                <?= e($campo['rotulo']) ?>
                                <?= $req ? '<span class="text-danger">*</span>' : '' ?>
                            </label>

                            <?php if (isset($campo['opcoes'])): ?>
                                <select id="<?= e($nome) ?>" name="<?= e($nome) ?>" class="form-select" <?= $req ?>>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($campo['opcoes'] as $op): ?>
                                        <option value="<?= e($op) ?>" <?= (string) $valor === (string) $op ? 'selected' : '' ?>>
                                            <?= e($op) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= e($tipo) ?>" id="<?= e($nome) ?>" name="<?= e($nome) ?>"
                                       value="<?= e($valor) ?>" class="form-control"
                                       <?= $tipo === 'number' ? 'min="0" step="' . e($campo['step'] ?? '1') . '"' : '' ?>
                                       <?= $req ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <?= $editando ? 'Atualizar' : 'Cadastrar' ?>
                    </button>
                    <?php if ($editando): ?>
                        <a href="<?= e(basename($_SERVER['SCRIPT_NAME'])) ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Registros cadastrados (<?= count($registros) ?>)</div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <?php foreach ($cfg['campos'] as $campo): ?>
                        <th><?= e($campo['rotulo']) ?></th>
                    <?php endforeach; ?>
                    <th class="text-end">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$registros): ?>
                    <tr><td colspan="<?= count($cfg['campos']) + 2 ?>" class="text-center text-muted py-4">
                        Nenhum registro ainda.
                    </td></tr>
                <?php endif; ?>

                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= e($r['id']) ?></td>
                        <?php foreach ($cfg['campos'] as $nome => $campo): ?>
                            <td><?= e($r[$nome] ?? '') ?></td>
                        <?php endforeach; ?>
                        <td class="text-end text-nowrap">
                            <a href="?editar=<?= e($r['id']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="post" class="d-inline"
                                  onsubmit="return confirm('Deseja realmente excluir este registro?');">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
