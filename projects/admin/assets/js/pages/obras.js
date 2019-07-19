$(function () {

    var last_id = 0;

    var cliente = new Tokens('#clientes', {
        url: 'admin/obras/clientes',
        type: 'post',
        names: ['id_usuario'],
        show: 'nome',
        cantReapeatComparison: ['id_usuario'],
        nothingMessage: 'Nenhum resultado encontrado.',
        load: clientes.data,
        afterRemove: function (data, token) {
            last_id = 0;
        },
        afterCreate: function (data, token) {
            last_id = data.id_usuario;
        },
        remote: function (data, token) {
            cliente.options.nothingMessage = 'Nenhum resultado encontrado.';
            if (token.tokenCount() == 1) {
                cliente.options.nothingMessage = 'Você só pode escolher um cliente. Para escolher outro, remova o atual.';
                token.process([]);
            } else {
                token.process(data.data);
            }
        }
    });

    $.alpaca.setDefaultLocale("pt_BR");

    $("#file-styled").uniform({
        fileButtonClass: 'action btn bg-blue'
    });

    $('.badge.badge-success .caret').on('click', function () {
        var self = $(this);
        $('.keep_files[data-id="' + self.attr('data-id') + '"]').attr('name', 'News[remove_files][]');
        self.closest('.badge.badge-success').remove();
    });

    $(document).on('change', '#file-styled', function () {
        const $input = $(this),
            filenames = $.map($input[0].files, function (file) {
                return file.name;
            });
        $input.siblings('.filename').html(filenames.join(', '));
    });

});