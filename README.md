# Gerenciador Logístico

Projeto da **Atividade Prática Orientada (APO)** de *Fundamentos de Programação para Internet* (UNIPAR EAD).

Aplicação web para controlar **fretes**: cadastra produtos, endereços (origem e destino) e transportadores, gera um frete ligando esses dados e emite um relatório dos fretes de um dia.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP 8 |
| Front-end | HTML5, CSS3, JavaScript |
| Visual | Bootstrap 5 (via CDN) |
| Armazenamento | Arquivos JSON na pasta `data/` (sem banco de dados) |
| Ambiente | XAMPP (Apache) |
| Versionamento | Git / GitHub |

## Funcionalidades

- Cadastro, edição, listagem e exclusão de **produtos**, **endereços** e **transportadores**.
- Geração de **frete**: um transportador leva **vários produtos** (relação 1 para muitos).
- Linhas de produto adicionadas **dinamicamente com JavaScript** na tela de frete.
- **Relatório de fretes por dia**, com totais de peso e valor, e **download em CSV**.
- Validações no servidor (campos obrigatórios, origem diferente de destino, quantidades).
- Bloqueio de exclusão de registros que já foram usados em um frete.
- Menu de navegação em todas as telas e layout responsivo.

## Como executar (XAMPP)

1. Instale o [XAMPP](https://www.apachefriends.org/) e inicie o **Apache**.
2. Copie a pasta do projeto para `C:\xampp\htdocs\gerenciador-logistico`.
3. Acesse **http://localhost/gerenciador-logistico/**.
4. A pasta `data/` precisa permitir escrita (no Windows já é o padrão).

> É necessário acesso à internet, pois o Bootstrap é carregado por CDN.

## Estrutura do projeto

```
gerenciador-logistico/
├── index.php              # Página inicial (resumo)
├── produtos.php           # Cadastro de produtos
├── enderecos.php          # Cadastro de endereços (origem/destino)
├── transportadores.php    # Cadastro de transportadores
├── fretes.php             # Geração e listagem de fretes
├── relatorio.php          # Relatório por dia + download CSV
├── includes/
│   ├── funcoes.php        # Leitura/gravação dos JSON e funções auxiliares
│   ├── crud.php           # CRUD genérico reaproveitado pelas 3 telas de cadastro
│   ├── header.php         # Cabeçalho HTML e menu de navegação
│   └── footer.php         # Rodapé e scripts
├── assets/
│   ├── css/estilo.css     # Estilos próprios
│   └── js/app.js          # JavaScript (campos dinâmicos do frete)
└── data/                  # Arquivos JSON gerados pelo sistema
```

## Como os dados se relacionam

```
Frete ──┬── 1 Transportador
        ├── 1 Endereço de origem
        ├── 1 Endereço de destino
        └── N Produtos (cada um com quantidade)
```

## Autor

Nome: _preencha_ · RA: _preencha_ · Curso: _preencha_ · Polo: _preencha_
