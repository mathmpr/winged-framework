$(function () {

    var cliente = new Tokens('#News_categorias', {
        url: window.protocol + window._parent + 'news-categorias/categorias',
        type: 'post',
        names: ['id_categoria'],
        show: 'categoria',
        cantReapeatComparison: ['id_categoria'],
        nothingMessage: 'Nenhum resultado encontrado.',
        load: newsCategorias,
        remote: function (data, token) {
            cliente.options.nothingMessage = 'Nenhum resultado encontrado.';
            token.process(data);
        }
    });

});