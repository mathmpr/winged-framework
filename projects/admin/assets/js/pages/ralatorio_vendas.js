$(function () {

    $('#RelatorioVendas_data_inicial, #RelatorioVendas_data_final').mask('99/99/9999 99:99:99');

    $('.uncollapse').on('click', function () {
        $(this).closest('div').find('[data-action="collapse"]').trigger('click');
    });

    new Tokens('#RelatorioVendas_clientes', {
        url: 'admin/carrinho/clientes',
        type: 'post',
        names: ['id_cliente'],
        show: 'nome',
        cantReapeatComparison: ['id_cliente'],
        nothingMessage: 'Nenhum resultado encontrado.',
        load: clientes.data,
        remote: function (data, token) {
            token.process(data.data);
        }
    });

    new Tokens('#RelatorioVendas_bairros', {
        url: 'admin/relatorio-vendas/bairros',
        type: 'post',
        names: ['id_bairro'],
        show: 'nome',
        cantReapeatComparison: ['id_bairro'],
        nothingMessage: 'Nenhum resultado encontrado.',
        load: bairros.data,
        remote: function (data, token) {
            token.process(data.data);
        }
    });

    new Tokens('#RelatorioVendas_produtos', {
        url: 'admin/pedidos/produtos',
        type: 'post',
        names: ['id_produto'],
        show: 'nome',
        cantReapeatComparison: ['id_produto'],
        nothingMessage: 'Nenhum resultado encontrado.',
        load: produtos.data,
        remote: function (data, token) {
            token.process(data.data);
        }
    });

    new Tokens('#RelatorioVendas_status', {
        url: 'admin/relatorio-vendas/status',
        type: 'post',
        names: ['id_status'],
        show: 'nome',
        cantReapeatComparison: ['id_status'],
        nothingMessage: 'Nenhum resultado encontrado.',
        load: _status.data,
        remote: function (data, token) {
            token.process(data.data);
        }
    });

    if (typeof tojs !== 'undefined') {

        $('#full_valor h5').html('Foram vendidos R$ ' + tojs.valor + ' entre as datas <code>' + tojs.inicial + '</code> e <code>' + tojs.final + '</code>');
        $('#full_quantidade h5').html(tojs.quantidade + ' produtos vendidos entre as datas <code>' + tojs.inicial + '</code> e <code>' + tojs.final + '</code>');
        $('#full_lucro h5').html('O lucro total foi de R$ ' + tojs.custo + ' entre as datas <code>' + tojs.inicial + '</code> e <code>' + tojs.final + '</code>');

        var fc = true;
        for (var i in tojs.produtos_valor) {
            var now = tojs.produtos_valor[i];
            var tr = $('<tr></tr>');
            if (fc === true) tr.addClass('bg-success-400');
            fc = false;
            tr.append('<td>' + now['nome'] + '</td>');
            tr.append('<td>' + now['quantidade'] + '</td>');
            tr.append('<td>R$ ' + now['valor'] + '</td>');
            $('#resumo_produtos_valor tbody').append(tr);
        }

        fc = true;
        for (i in tojs.produtos_quantidade) {
            now = tojs.produtos_quantidade[i];
            tr = $('<tr></tr>');
            if (fc === true) tr.addClass('bg-success-400');
            fc = false;
            tr.append('<td>' + now['nome'] + '</td>');
            tr.append('<td>' + now['quantidade'] + '</td>');
            tr.append('<td>R$ ' + now['valor'] + '</td>');
            $('#resumo_produtos_quantidade tbody').append(tr);
        }

        fc = true;
        for (i in tojs.sellers) {
            now = tojs.sellers[i];
            tr = $('<tr></tr>');
            if (fc === true) tr.addClass('bg-success-400');
            fc = false;
            tr.append('<td>' + now['nome'] + '</td>');
            tr.append('<td>' + now['quantidade'] + '</td>');
            tr.append('<td>R$ ' + now['valor'] + '</td>');
            $('#resumo_vendedores tbody').append(tr);
        }

        fc = true;
        for (i in tojs.acheters) {
            now = tojs.acheters[i];
            tr = $('<tr></tr>');
            if (fc === true) tr.addClass('bg-success-400');
            fc = false;
            tr.append('<td>' + now['nome'] + '</td>');
            tr.append('<td>' + now['quantidade'] + '</td>');
            tr.append('<td>R$ ' + now['valor'] + '</td>');
            $('#resumo_clientes tbody').append(tr);
        }
    }

});