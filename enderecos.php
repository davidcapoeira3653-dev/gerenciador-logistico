<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/crud.php';

$ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE',
        'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

// Um endereço serve tanto como origem quanto como destino: quem decide é o frete.
$cfg = [
    'entidade' => 'enderecos',
    'titulo'   => 'Endereços (origem e destino)',
    'campos'   => [
        'apelido'    => ['rotulo' => 'Identificação (ex.: Depósito Central)', 'obrigatorio' => true, 'col' => 6],
        'cep'        => ['rotulo' => 'CEP', 'col' => 3],
        'numero'     => ['rotulo' => 'Número', 'col' => 3],
        'logradouro' => ['rotulo' => 'Logradouro', 'obrigatorio' => true, 'col' => 6],
        'bairro'     => ['rotulo' => 'Bairro', 'col' => 6],
        'cidade'     => ['rotulo' => 'Cidade', 'obrigatorio' => true, 'col' => 8],
        'uf'         => ['rotulo' => 'UF', 'obrigatorio' => true, 'opcoes' => $ufs, 'col' => 4],
    ],
];

processarCrud($cfg);

$tituloPagina = 'Endereços';
require __DIR__ . '/includes/header.php';
renderCrud($cfg);
require __DIR__ . '/includes/footer.php';
