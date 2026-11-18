/**
 * JavaScript do sistema.
 * Na tela de Fretes, permite adicionar/remover linhas de produtos dinamicamente.
 */
document.addEventListener('DOMContentLoaded', function () {
    var lista = document.getElementById('lista-itens');
    var modelo = document.getElementById('modelo-item');
    var botaoAdicionar = document.getElementById('btn-adicionar-item');

    // Só executa nas páginas que têm a lista de itens (fretes.php)
    if (!lista || !modelo || !botaoAdicionar) {
        return;
    }

    // Desabilita o botão "remover" quando só existe uma linha
    function atualizarBotoes() {
        var linhas = lista.querySelectorAll('.item-frete');
        linhas.forEach(function (linha) {
            linha.querySelector('.btn-remover-item').disabled = linhas.length === 1;
        });
    }

    // Copia o <template> e acrescenta uma nova linha na lista
    function adicionarLinha() {
        lista.appendChild(modelo.content.cloneNode(true));
        atualizarBotoes();
    }

    botaoAdicionar.addEventListener('click', adicionarLinha);

    // Remove a linha clicada (delegação de evento: um único ouvinte na lista inteira)
    lista.addEventListener('click', function (evento) {
        var botao = evento.target.closest('.btn-remover-item');
        if (botao) {
            botao.closest('.item-frete').remove();
            atualizarBotoes();
        }
    });

    adicionarLinha(); // começa com uma linha
});
