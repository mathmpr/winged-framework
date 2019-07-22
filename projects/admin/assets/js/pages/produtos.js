$(function () {
    $('#Produtos_id_categoria').on('change', function () {
        var select = $('#Produtos_id_subcategoria').find('option:first-child').clone().text('Carregando...');
        $('#Produtos_id_subcategoria').html('').append(select);
        $.ajax({
            url: window.protocol + window._parent + 'services/get-subcategorias/',
            type: 'post',
            data: {id_categoria: $(this).val()},
            success: function (response) {
                response = json(response, true);
                if (response) {
                    if (response.status) {
                        var select = $('#Produtos_id_subcategoria').find('option:first-child').clone().text('Selecione uma subcategoria');
                        $('#Produtos_id_subcategoria').html('').append(select);
                        for (var i in response.data) {
                            $('#Produtos_id_subcategoria').append('<option value="' + i + '">' + response.data[i] + '</option>');
                        }
                    }
                }
            }
        })
    });
    $('#Produtos_valor_minimo').maskMoney();
    $('#Produtos_valor_de_custo').maskMoney();
$('#Produtos_valor_unitario').maskMoney();
$('#Produtos_valor_atacado').maskMoney();
});