$(function () {
    $('#EnderecosClientes_id_estado').on('change', function () {
        var select = $('#EnderecosClientes_id_cidade').find('option:first-child').clone().text('Carregando...');
        $('#EnderecosClientes_id_cidade').html('').append(select);
        $.ajax({
            url: window.protocol + 'admin/services/get-cidades/',
            type: 'post',
            data: {id_estado: $(this).val()},
            success: function (response) {
                response = json(response, true);
                if (response) {
                    if (response.status) {
                        var select = $('#EnderecosClientes_id_cidade').find('option:first-child').clone().text('Selecione uma cidade');
                        $('#EnderecosClientes_id_cidade').html('').append(select);
                        for (var i in response.data) {
                            $('#EnderecosClientes_id_cidade').append('<option value="' + i + '">' + response.data[i] + '</option>');
                        }
                    }
                }
            }
        })
    });
    $('#EnderecosClientes_id_cidade, #EnderecosClientes_id_estado').on('change', function () {
        var select = $('#EnderecosClientes_id_bairro').find('option:first-child').clone().text('Carregando...');
        $('#EnderecosClientes_id_bairro').html('').append(select);
        $.ajax({
            url: window.protocol + 'admin/services/get-bairros/',
            type: 'post',
            data: {
                id_estado: $('#EnderecosClientes_id_estado').val() == '' ? 0 : $('#EnderecosClientes_id_estado').val(),
                id_cidade: $('#EnderecosClientes_id_cidade').val() == '' ? 0 : $('#EnderecosClientes_id_cidade').val(),
            },
            success: function (response) {
                response = json(response, true);
                if (response) {
                    if (response.status === true) {
                        var select = $('#EnderecosClientes_id_bairro').find('option:first-child').clone().text('Selecione um bairro');
                        $('#EnderecosClientes_id_bairro').html('').append(select);
                        var enter = false;
                        for (var i in response.data) {
                            enter = true;
                            $('#EnderecosClientes_id_bairro').append('<option value="' + i + '">' + response.data[i] + '</option>');
                        }
                        if(enter === false){
                            var select = $('#EnderecosClientes_id_bairro').find('option:first-child').clone().text('Nenhum bairro encontrado');
                            $('#EnderecosClientes_id_bairro').html('').append(select);
                        }
                    }else{
                        var select = $('#EnderecosClientes_id_bairro').find('option:first-child').clone().text('Selecione uma cidade e estado');
                        $('#EnderecosClientes_id_bairro').html('').append(select);
                    }
                }
            }
        })
    });
});