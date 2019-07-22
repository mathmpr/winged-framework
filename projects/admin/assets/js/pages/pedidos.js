var search_objects = {};

function getForm(_) {
    var form = $('<form></form>');
    form.append('<input name="id_pedido_produto" type="hidden" value="' + _.closest('tr').attr('data-id-pedido-produto') + '">');
    form.append('<input name="id_pedido" type="hidden" value="' + _.closest('tr').attr('data-id') + '">');
    form.append('<input name="quantidade" type="hidden" value="' + _.closest('tr').find('.quantidade').val() + '"/>');
    if (_.closest('tr').find('.valor_unico').val() == '') {
        form.append('<input name="valor_unico" type="hidden" value="0"/>');
    } else {
        form.append('<input name="valor_unico" type="hidden" value="' + _.closest('tr').find('.valor_unico').val() + '"/>');
    }
    form.append('<input name="porcentagem_desconto" type="hidden" value="' + _.closest('tr').find('.porcentagem_desconto').val() + '"/>');
    return form;
}

function attForm(_, response) {
    var tr = _.closest('tr');
    var id = tr.attr('data-id');
    tr.find('.quantidade').val(response.quantidade);
    tr.find('.quantidade').attr('data-old', response.quantidade);
    tr.find('.valor_unico').val(response.valor_unico);
    tr.find('.valor_unico').attr('data-old', response.valor_unico);
    tr.find('.porcentagem_desconto').val(response.porcentagem_desconto);
    tr.find('.porcentagem_desconto').attr('data-old', response.porcentagem_desconto);
    tr.find('.valor_final').html('R$ ' + response.valor_final);
    $('tr[data-id=' + id + '].main').find('.quantidade_total').html(response.quantidade_total);
    $('tr[data-id=' + id + '].main').find('.valor_total').html('R$ ' + response.valor_total);
    $('tr[data-id=' + id + '].main').find('.media_desconto').html(response.media_desconto);
}

function rebindFields() {
    $('.valor_unico').maskMoney();
    $('.quantidade').numeric({
        decimal: false
    });
    $('.porcentagem_desconto').numeric({
        decimal: ','
    });
    $('.porcentagem_desconto, .valor_unico, .quantidade').unbind('blur');
    $('.porcentagem_desconto, .valor_unico, .quantidade').on('blur', function () {
        var _ = $(this);
        if (_.attr('data-old') != _.val()) {
            var data = gem_data(serialize(getForm(_)));
            $.ajax({
                url: 'pedidos/refresh',
                type: 'post',
                data: data,
                contentType: false,
                processData: false,
                success: function (response) {
                    response = json(response);
                    if (response) {
                        if (response.status) {
                            if (response.error) {
                                new PNotify({
                                    title: 'Erro',
                                    text: response.error,
                                    addclass: 'bg-danger'
                                });
                            } else {
                                new PNotify({
                                    title: 'Sucesso',
                                    text: 'O campo ' + _.attr('field-name') + ' foi atualizado com sucesso.',
                                    addclass: 'bg-success'
                                });
                            }
                            attForm(_, response);
                        } else {
                            new PNotify({
                                title: 'Erro',
                                text: response.message,
                                addclass: 'bg-danger'
                            });
                        }
                    }
                }
            });
        }
    });

    $('.remove-unic').unbind('click');
    $('.remove-unic').on('click', function () {
        var _ = $(this);
        var tr = _.closest('tr');
        $('#remove-item').attr('data-id', tr.attr('data-id-pedido-produto'));
        $('#comfirm-remove').trigger('click');
    });
}

$(function () {

    $('label[for=select_all]').on('click', function () {
        var _ = $(this);
        var tr = _.closest('thead').next().find('tr').eq(0);
        tr.find('.checkbox label').trigger('click');
        while (tr.next().length > 0) {
            tr = tr.next();
            tr.find('.checkbox label').trigger('click');
        }
    });

    $('#gem_pdf_dia').on('click', function () {
        var len = $('#DataTables_Table_1_wrapper').find('input[name=id_pedido]:checked');
        if (len.length > 0) {
            var form = $('<form method="post" action="pedidos-hoje/baixa-estoque/"></form>');
            len.each(function () {
                form.append('<input type="hidden" name="id_pedido[]" value="' + $(this).val() + '"/>');
            });
            $('body').append(form);
            form[0].submit();
        } else {
            new PNotify({
                title: 'Erro',
                text: 'Selecione alguns pedidos para gerar o PDF.',
                addclass: 'bg-danger'
            });
        }
    });

    $('#valor_frete').maskMoney();

    rebindFields();

    $('#agendamento').mask('99/99/9999 99:99');

    $('.details').on('click', function () {
        var _ = $(this);
        var tr = _.closest('tr');
        var id = tr.attr('data-id');
        $('tr[data-id=' + id + '].minus').stop().slideToggle();
        _.toggleClass('_close');
    });

    $('.edit-pedido').on('click', function () {
        var _ = $(this);
        var tr = _.closest('tr');
        var id = tr.attr('data-id');
        var status = tr.attr('data-status');

        $('#id_pedido').val(id);

        $('#innf' + tr.find('input[name=innf]').val()).trigger('click');
        $('#agendamento').val(tr.find('input[name=agendamento]').val());
        $('#observacoes').val(tr.find('input[name=observacoes]').val());
        $('#metodo_pagamento').val(tr.find('input[name=metodo_pagamento]').val());
        $('#valor_frete').val(tr.find('input[name=valor_frete]').val());

        $('#status').html('' +
            '<option value="1">Aguardando separação</option>' +
            '<option value="2">Encaminhado</option>' +
            '<option value="3">Entregue</option>' +
            '<option value="4">Cancelado</option>' +
            '');

        $('#status').find('option[value=' + status + ']').attr('selected', 'selected');

        $('#comfirm-update').trigger('click');
    });

    $('#render-update').on('click', function () {

        if ($('#status').val() == '') {
            new PNotify({
                title: 'Erro',
                text: 'Selecione um status para o pedido.',
                addclass: 'bg-danger'
            });
        } else {
            var data = gem_data(serialize($('#modal-update').find('form')));
            var tr = $('tr[data-id=' + $('#id_pedido').val() + ']');

            $.ajax({
                url: 'pedidos/update',
                type: 'post',
                data: data,
                contentType: false,
                processData: false,
                success: function (response) {

                    response = json(response);
                    if (response) {
                        if (response.status) {

                            tr.find('input[name=innf]').val(response.innf);

                            if (response.innf == 1) {
                                tr.css({
                                    background: 'rgb(0, 230, 248)',
                                    color: '#fff'
                                });
                            }else{
                                tr.css({
                                    background: '#fff',
                                    color: '#333333'
                                });
                            }

                            tr.find('span.label').html(response.st_text);
                            tr.find('span.label').attr('class', 'label label-' + response.st_color);

                            tr.find('input[name=agendamento]').val($('#agendamento').val());
                            tr.find('input[name=observacoes]').val($('#observacoes').val());
                            tr.find('input[name=metodo_pagamento]').val($('#metodo_pagamento').val());
                            tr.find('input[name=valor_frete]').val($('#valor_frete').val());
                            tr.find('.valor_frete').html('R$ ' + $('#valor_frete').val());
                            tr.find('.agendamento').html($('#agendamento').val());
                            tr.find('.valor_total').html('R$ ' + response.valor_total);
                            tr.attr('data-status', $('#status').val());

                            if (response.current == 4) {
                                tr.find('.edit-pedido').unbind('click');
                                tr.find('.edit-pedido').css({opacity: '0.4'});
                                tr.find('.btn.bg-danger').css({opacity: '1'});
                                tr.find('.btn.bg-danger').attr('onclick', 'confirmDelete(\'' + window.protocol + 'pedidos/delete/' + $('#id_pedido').val() + '\', \'' + location.href + '\')');
                            }

                            if (response.current == 3) {
                                tr.find('.edit-pedido').unbind('click');
                                tr.find('.edit-pedido').css({opacity: '0.4'});
                            }

                            if (response.warn) {
                                new PNotify({
                                    title: 'Aviso',
                                    text: response.warn,
                                    addclass: 'bg-orange-400'
                                });
                            }
                        } else {
                            new PNotify({
                                title: 'Erro',
                                text: response.message,
                                addclass: 'bg-danger'
                            });
                        }
                    }
                }
            });
            $('#modal-update').trigger('click');
        }
    });

    $('input[name=produto]').each(function () {
        var key_to_object = gem_token(18);
        var _ = $(this);
        _.attr('key-to-object', key_to_object);
        search_objects[key_to_object] = new Tokens(_, {
            url: 'pedidos/produtos',
            type: 'post',
            names: ['id_produto'],
            show: 'nome',
            cantReapeatComparison: ['id_produto'],
            nothingMessage: 'Nenhum resultado encontrado.',
            remote: function (data, token) {
                token.process(data.data);
            }
        });
    });

    $('.addnew').on('click', function () {
        var _ = $(this);

        _.html('<i class="icon-spinner11 infinite-rotation"></i>');

        var tr = _.closest('tr');
        var id = tr.attr('data-id');

        var key_to_object = tr.find('input[key-to-object]').attr('key-to-object');

        var data = gem_data(serialize($('<form></form>').append(search_objects[key_to_object].main.clone()).append('<input name="id_pedido" type="hidden" value="' + id + '"/>')));

        $.ajax({
            url: 'pedidos/add',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {
                setTimeout(function () {
                    _.html('<i class="icon-plus2"></i>');
                    response = json(response);
                    if (response) {
                        if (response.status) {

                            $('tr[data-id=' + id + '].main').find('.quantidade_total').html(response.quantidade_total);
                            $('tr[data-id=' + id + '].main').find('.valor_total').html('R$ ' + response.valor_total);
                            $('tr[data-id=' + id + '].main').find('.media_desconto').html(response.media_desconto);

                            search_objects[key_to_object].removeAll();
                            for (var i in response.data.updates) {
                                var now = response.data.updates[i];
                                if (now.error) {
                                    new PNotify({
                                        title: 'Erro',
                                        text: 'Não possuímos o produto \'' + now.nome + '\' em estoque.',
                                        addclass: 'bg-danger'
                                    });
                                } else {
                                    new PNotify({
                                        title: 'Sucesso',
                                        text: 'Uma unidade do produto \'' + now.nome + '\' foi adicionado ao pedido.',
                                        addclass: 'bg-success'
                                    });
                                    $('tr[data-id-pedido-produto=' + now.id_pedido_produto + '] .quantidade').val(now.quantidade);
                                    $('tr[data-id-pedido-produto=' + now.id_pedido_produto + '] .valor_unico').val(now.valor_unico);
                                    $('tr[data-id-pedido-produto=' + now.id_pedido_produto + '] .porcentagem_desconto').val(now.porcentagem_desconto);
                                    $('tr[data-id-pedido-produto=' + now.id_pedido_produto + '] .valor_final').html('R$ ' + now.valor_final);
                                }
                            }

                            for (var i in response.data.inserts) {
                                var now = response.data.inserts[i];
                                if (now.id_pedido_produto == null || now.id_pedido_produto == false) {
                                    new PNotify({
                                        title: 'Erro',
                                        text: 'Não possuímos o produto \'' + now.nome + '\' em estoque.',
                                        addclass: 'bg-danger'
                                    });
                                } else {
                                    new PNotify({
                                        title: 'Sucesso',
                                        text: 'Uma unidade do produto \'' + now.nome + '\' foi adicionado ao pedido.',
                                        addclass: 'bg-success'
                                    });

                                    var clone = tr.prev().clone();
                                    clone.attr('data-id-pedido-produto', now.id_pedido_produto);
                                    clone.css({display: 'none'});
                                    clone.find('.quantidade').val(now.quantidade);
                                    clone.find('.valor_unico').val(now.valor_unico);
                                    clone.find('.porcentagem_desconto').val(now.porcentagem_desconto);
                                    clone.find('.valor_final').html('R$ ' + now.valor_final);
                                    clone.find('td').eq(1).html(now.nome);
                                    tr.before(clone);
                                    rebindFields();
                                }
                            }
                            $('tr[data-id=' + id + ']').fadeIn(500);
                        } else {
                            new PNotify({
                                title: 'Erro',
                                text: response.message,
                                addclass: 'bg-danger'
                            });
                        }
                    }
                }, 2000);
            }
        });
    });

    $('#remove-item').on('click', function () {
        var _ = $(this);
        var tr = $('tr[data-id-pedido-produto=' + _.attr('data-id') + ']');
        var form = $('<form></form>');
        form.append('<input name="id_pedido_produto" type="hidden" value="' + tr.attr('data-id-pedido-produto') + '">');
        form.append('<input name="id_pedido" type="hidden" value="' + tr.attr('data-id') + '">');
        var data = gem_data(serialize(form));
        $.ajax({
            url: 'pedidos/remove',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {

                        tr.fadeOut(400, function () {

                            var main_id = tr.attr('data-id');

                            tr.remove();

                            $('tr[data-id=' + main_id + '].main').find('.quantidade_total').html(response.quantidade_total);
                            $('tr[data-id=' + main_id + '].main').find('.valor_total').html('R$ ' + response.valor_total);
                            $('tr[data-id=' + main_id + '].main').find('.media_desconto').html(response.media_desconto);

                            if (parseInt(response.quantidade_total) == 0) {
                                new PNotify({
                                    title: 'Sucesso',
                                    text: 'Seu pedido ficou sem nenhum item, esse pedido foi deletado dos registros.',
                                    addclass: 'bg-success'
                                });
                                setTimeout(function () {
                                    $('tr[data-id=' + main_id + ']').fadeOut(400, function () {
                                        $('tr[data-id=' + main_id + ']').remove();
                                    })
                                }, 400);
                            } else {
                                new PNotify({
                                    title: 'Sucesso',
                                    text: 'O item foi removido e seu pedido foi salvo com sucesso.',
                                    addclass: 'bg-success'
                                });
                            }
                        });
                    } else {
                        new PNotify({
                            title: 'Erro',
                            text: response.message,
                            addclass: 'bg-danger'
                        });
                    }
                }
            }
        });
    });


});