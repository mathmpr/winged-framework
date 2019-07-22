$(function () {
    $('#Bairros_id_estado').on('change', function () {
        var select = $('#Bairros_id_cidade').find('option:first-child').clone().text('Carregando...');
        $('#Bairros_id_cidade').html('').append(select);
        $.ajax({
            url: window.protocol + window._parent + 'services/get-cidades/',
            type: 'post',
            data: {id_estado: $(this).val()},
            success: function (response) {
                response = json(response, true);
                if (response) {
                    if (response.status) {
                        var select = $('#Bairros_id_cidade').find('option:first-child').clone().text('Selecione uma cidade');
                        $('#Bairros_id_cidade').html('').append(select);
                        for (var i in response.data) {
                            $('#Bairros_id_cidade').append('<option value="' + i + '">' + response.data[i] + '</option>');
                        }
                    }
                }
            }
        })
    });
});