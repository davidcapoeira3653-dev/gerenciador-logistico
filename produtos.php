<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/crud.php';

$cfg = [
    'entidade' => 'produtos',
    'titulo'   => 'Produtos',
    'campos'   => [
        'nome'      => ['rotulo' => 'Nome', 'obrigatorio' => true, 'col' => 6],
        'descricao' => ['rotulo' => 'Descrição', 'col' => 6],
        'peso'      => ['rotulo' => 'Peso (kg)', 'tipo' => 'number', 'step' => '0.01', 'col' => 6],
        'valor'     => ['rotulo' => 'Valor unitário (R$)', 'tipo' => 'number', 'step' => '0.01', 'col' => 6],
    ],
];

processarCrud($cfg);          // trata POST (salvar/excluir) e redireciona

$tituloPagina = 'Produtos';
require __DIR__ . '/includes/header.php';
renderCrud($cfg);
require __DIR__ . '/includes/footer.php';
