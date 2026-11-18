<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/crud.php';

$cfg = [
    'entidade' => 'transportadores',
    'titulo'   => 'Transportadores',
    'campos'   => [
        'nome'      => ['rotulo' => 'Nome / Razão social', 'obrigatorio' => true, 'col' => 6],
        'documento' => ['rotulo' => 'CPF ou CNPJ', 'col' => 6],
        'telefone'  => ['rotulo' => 'Telefone', 'tipo' => 'tel', 'col' => 6],
        'veiculo'   => ['rotulo' => 'Veículo / Placa', 'col' => 6],
    ],
];

processarCrud($cfg);

$tituloPagina = 'Transportadores';
require __DIR__ . '/includes/header.php';
renderCrud($cfg);
require __DIR__ . '/includes/footer.php';
