$(function () {
    $('#Clientes_cep, #Obras_cep').on('blur', function () {
        var self = $(this);
        $.ajax({
            url: 'services/get-cep',
            type: 'post',
            data: {
                cep: self.val()
            },
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {
                        $('#Clientes_bairro, #Obras_bairro').html('<option value="' + response.data.bairro.pk + '" selected>' + response.data.bairro.value + '</option>');
                        $('#Clientes_cidade, #Obras_cidade').html('<option value="' + response.data.cidade.pk + '" selected>' + response.data.cidade.value + '</option>');
                        $('#Clientes_estado, #Obras_estado').html('<option value="' + response.data.estado.pk + '" selected>' + response.data.estado.value + '</option>');
                        $('#Clientes_rua, #Obras_rua').val(response.data.rua);
                    }
                }
            }
        });
    });
});
