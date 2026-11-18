<?php
/**
 * Funções compartilhadas por todo o sistema.
 * Os dados ficam em arquivos JSON na pasta /data (sem banco de dados).
 */
session_start();
date_default_timezone_set('America/Sao_Paulo');

const DATA_DIR = __DIR__ . '/../data';

/* ---------- Utilidades ---------- */

// Escapa texto antes de imprimir em HTML (evita injeção de código na página)
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Guarda uma mensagem para ser exibida na próxima página
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $mensagem];
}

// Redireciona o navegador (usa o cabeçalho HTTP Location) e encerra o script
function redirecionar(string $url)
{
    header("Location: $url");
    exit;
}

/* ---------- "Banco" em arquivos JSON ---------- */

function arquivo(string $entidade): string
{
    return DATA_DIR . "/$entidade.json";
}

// Lê todos os registros de uma entidade (produtos, enderecos, ...)
function ler(string $entidade): array
{
    $arq = arquivo($entidade);
    if (!is_file($arq)) {
        return [];
    }
    $dados = json_decode(file_get_contents($arq), true);
    return is_array($dados) ? $dados : [];
}

// Grava a lista inteira de volta no arquivo
function gravar(string $entidade, array $lista): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }
    file_put_contents(
        arquivo($entidade),
        json_encode(array_values($lista), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

// Busca um registro pelo id (ou null se não existir)
function buscar(string $entidade, $id): ?array
{
    foreach (ler($entidade) as $registro) {
        if ((string) $registro['id'] === (string) $id) {
            return $registro;
        }
    }
    return null;
}

// Cria (sem id) ou atualiza (com id) um registro
function salvar(string $entidade, array $dados): array
{
    $lista = ler($entidade);

    if (empty($dados['id'])) {
        $ids = array_column($lista, 'id');
        $dados['id'] = $ids ? max($ids) + 1 : 1;
        $lista[] = $dados;
    } else {
        $dados['id'] = (int) $dados['id'];
        foreach ($lista as $i => $registro) {
            if ($registro['id'] === $dados['id']) {
                $lista[$i] = $dados;
            }
        }
    }

    gravar($entidade, $lista);
    return $dados;
}

function excluir(string $entidade, $id): void
{
    $lista = array_filter(ler($entidade), fn($r) => (string) $r['id'] !== (string) $id);
    gravar($entidade, $lista);
}

// Impede apagar produto/endereço/transportador que já foi usado em um frete
function emUso(string $entidade, $id): bool
{
    foreach (ler('fretes') as $frete) {
        if ($entidade === 'transportadores' && (string) $frete['transportador_id'] === (string) $id) {
            return true;
        }
        if ($entidade === 'enderecos'
            && in_array((string) $id, [(string) $frete['origem_id'], (string) $frete['destino_id']], true)) {
            return true;
        }
        if ($entidade === 'produtos') {
            foreach ($frete['itens'] as $item) {
                if ((string) $item['produto_id'] === (string) $id) {
                    return true;
                }
            }
        }
    }
    return false;
}

/* ---------- Fretes ---------- */

function textoEndereco(?array $end): string
{
    if (!$end) {
        return '(removido)';
    }
    $num = $end['numero'] !== '' ? ', ' . $end['numero'] : '';
    return "{$end['apelido']} - {$end['logradouro']}$num - {$end['cidade']}/{$end['uf']}";
}

// Monta uma linha por item de frete, já com os nomes (usada na tela e no CSV)
function linhasRelatorio(string $data): array
{
    $linhas = [];
    foreach (ler('fretes') as $frete) {
        if ($frete['data'] !== $data) {
            continue;
        }
        $transp = buscar('transportadores', $frete['transportador_id']);
        $origem = textoEndereco(buscar('enderecos', $frete['origem_id']));
        $destino = textoEndereco(buscar('enderecos', $frete['destino_id']));

        foreach ($frete['itens'] as $item) {
            $prod = buscar('produtos', $item['produto_id']);
            $linhas[] = [
                'frete'         => $frete['id'],
                'data'          => $frete['data'],
                'transportador' => $transp['nome'] ?? '(removido)',
                'origem'        => $origem,
                'destino'       => $destino,
                'produto'       => $prod['nome'] ?? '(removido)',
                'quantidade'    => $item['quantidade'],
                'peso_total'    => ($prod['peso'] ?? 0) * $item['quantidade'],
                'valor_total'   => ($prod['valor'] ?? 0) * $item['quantidade'],
            ];
        }
    }
    return $linhas;
}

function dinheiro($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function dataBR(string $dataIso): string
{
    $d = DateTime::createFromFormat('Y-m-d', $dataIso);
    return $d ? $d->format('d/m/Y') : $dataIso;
}
