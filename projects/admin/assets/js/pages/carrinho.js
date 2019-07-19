function getForm(_) {
    var form = $('<form></form>');
    form.append('<input name="id_carrinho" type="hidden" value="' + _.closest('tr').attr('data-id') + '">');
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
    var table = _.closest('table');
    tr.find('.quantidade').val(response.quantidade);
    tr.find('.quantidade').attr('data-old', response.quantidade);
    tr.find('.valor_unico').val(response.valor_unico);
    tr.find('.valor_unico').attr('data-old', response.valor_unico);
    tr.find('.porcentagem_desconto').val(response.porcentagem_desconto);
    tr.find('.porcentagem_desconto').attr('data-old', response.porcentagem_desconto);
    tr.find('.valor_final').html('R$ ' + response.valor_final);
    tr.find('.valor_unitario_final').html('R$ ' + response.valor_unitario_final);
    table.find('.quantidade_total').html(response.quantidade_total);
    table.find('.valor_total').html('R$ ' + response.valor_total);
}

function rebindFields() {
    setTimeout(function(){
        $('.porcentagem_desconto, .valor_unico, .quantidade').unbind('blur')
        $('.porcentagem_desconto, .valor_unico, .quantidade').on('blur', function () {
            var _ = $(this);
            if (_.attr('data-old') != _.val()) {
                var data = gem_data(serialize(getForm(_)));
                $.ajax({
                    url: 'admin/carrinho/refresh',
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
            $('#remove-item').attr('data-id', tr.attr('data-id'));
            $('#comfirm-remove').trigger('click');
        });
    }, 200);
}

$(function () {

    rebindFields();

    $('.valor_unico').maskMoney();
    $('.quantidade').numeric({
        decimal: false
    });
    $('.porcentagem_desconto').numeric({
        decimal: ','
    });

    $('#add-verify').on('click', function () {
        $('#finalizar-pedido').addClass('verified');
    });

    $('#finalizar-pedido').on('click', function () {

        var _ = $(this);

        if (cliente.tokenCount() == 0) {
            $('#comfirm-delete').trigger('click');
            return;
        }

        _.html('<i class="icon-spinner11 infinite-rotation"></i>');

        if (_.hasClass('verified')) {
            $.ajax({
                url: 'admin/carrinho/finish',
                type: 'post',
                success: function (response) {
                    setTimeout(function () {
                        _.html('Finalizar pedido');
                        response = json(response);
                        if (response) {
                            if (response.status) {
                                $('#id_pedido').html(response.id_pedido);
                                $('#pedido_success').next().next().fadeOut(1);
                                $('#pedido_success').next().fadeOut(1);
                                setTimeout(function () {
                                    $('#pedido_success').fadeIn(200);
                                }, 5);
                            } else {
                                if (response.empty) {
                                    new PNotify({
                                        title: 'Erro',
                                        text: 'Seu carrinho está vazio. Parece que alguém finalizou esse pedido enquanto você estava verificando os dados do carrinho. Você será direcionado aos produtos para montar um novo carrinho.',
                                        addclass: 'bg-danger'
                                    });
                                    setTimeout(function () {
                                        window.location = 'produtos';
                                    }, 2000);
                                } else {
                                    new PNotify({
                                        title: 'Erro',
                                        text: 'Enquanto você verificava o carrinho, alguns dados do banco de dados foram alterados por outro usúario. Sua página será atualizada e os campos com erro serão sinalizados em vermelho.',
                                        addclass: 'bg-danger'
                                    });
                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                                }

                            }
                        }
                    }, 2000);
                }
            });
        } else {
            $.ajax({
                url: 'admin/carrinho/verify',
                type: 'post',
                success: function (response) {
                    setTimeout(function () {
                        _.html('Finalizar pedido');
                        response = json(response);
                        if (response) {
                            if (response.status) {
                                $('#comfirm-verify').trigger('click');
                            } else {
                                if (response.empty) {
                                    new PNotify({
                                        title: 'Erro',
                                        text: 'Seu carrinho está vazio. Parece que alguém finalizou esse pedido enquanto você esteve fora. Você será direcionado aos produtos para montar um novo carrinho.',
                                        addclass: 'bg-danger'
                                    });
                                    setTimeout(function () {
                                        window.location = 'produtos';
                                    }, 2000);
                                } else {
                                    new PNotify({
                                        title: 'Erro',
                                        text: 'Existem alguns problemas com o seu carrinho. Sua página será atualizada e os campos com erro serão sinalizados em vermelho.',
                                        addclass: 'bg-danger'
                                    });
                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                                }
                            }
                        }
                    }, 2000);
                }
            });
        }
    });

    $('#valor_frete').maskMoney();

    $('#agendamento').mask('99/99/9999 99:99');

    var cliente = null;

    var last_id = 0;

    if (typeof clientes !== 'undefined') {
        if (clientes.data.length > 0) {
            last_id = clientes.data[0].id_cliente;
        }
    } else {
        clientes = {
            data: []
        }
    }


    $('#valor_frete, #agendamento, #metodo_pagamento, #observacoes').on('blur', function () {
        var _ = $(this);
        if (_.attr('data-old') != _.val()) {
            saveCarrinho();
        }
    });

    $('[name="innf"]').on('click', function () {
        saveCarrinho();
    });

    cliente = new Tokens('#clientes', {
        url: 'admin/carrinho/clientes',
        type: 'post',
        names: ['id_cliente'],
        show: 'nome',
        cantReapeatComparison: ['id_cliente'],
        nothingMessage: 'Nenhum resultado encontrado.',
        load: clientes.data,
        afterRemove: function (data, token) {
            last_id = 0;
            saveCarrinho(function () {
                $('#enderecos').fadeOut(200);
            });
        },
        afterCreate: function (data, token) {
            last_id = data.id_cliente;
            saveCarrinho();
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

    $('#select_enderecos').on('change', function () {
        saveCarrinho();
    });


    function saveCarrinho(func) {

        func = func || false;
        var form = $('<form></form>');
        form.append('<input name="id_cliente" type="hidden" value="' + last_id + '">');
        form.append('<input name="valor_frete" type="hidden" value="' + $('#valor_frete').val() + '"/>');
        form.append('<input name="agendamento" type="hidden" value="' + $('#agendamento').val() + '"/>');
        form.append('<input name="metodo_pagamento" type="hidden" value="' + $('#metodo_pagamento').val() + '"/>');
        form.append('<input name="observacoes" type="hidden" value="' + $('#observacoes').val() + '"/>');
        form.append('<input name="select_enderecos" type="hidden" value="' + $('#select_enderecos').val() + '"/>');
        form.append('<input name="innf" type="hidden" value="' + $('[name="innf"]:checked').val() + '"/>');
        var data = gem_data(serialize(form));
        $.ajax({
            url: 'admin/carrinho/save',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {

                        $('.quantidade_total').html(response.quantidade_total);
                        $('.valor_total').html('R$ ' + response.valor_total);
                        $('#valor_frete').attr('data-old', $('#valor_frete').val());
                        $('#agendamento').attr('data-old', $('#agendamento').val() + ':00');
                        $('#observacoes').attr('data-old', $('#observacoes').val());
                        $('#metodo_pagamento').attr('data-old', $('#metodo_pagamento').val());

                        $('#select_enderecos').html('');


                        if (response.enderecos.length) {
                            for (var i in response.enderecos) {
                                $('#select_enderecos').append('<option value="' + response.enderecos[i].value + '" ' + (response.enderecos[i].check ? 'selected' : '') + '>' + response.enderecos[i].text + '</option>');
                            }
                            if (func) {
                                func();
                            } else {
                                $('#enderecos').fadeIn(200);
                            }
                        }

                        if (response.warn) {
                            new PNotify({
                                title: 'Aviso',
                                text: response.warn,
                                addclass: 'bg-orange-400'
                            });
                        }

                        new PNotify({
                            title: 'Sucesso',
                            text: 'O carrinho foi salvo com sucesso.',
                            addclass: 'bg-success'
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
    }

    $('#remove-all').on('click', function () {
        $('#comfirm-remove-all').trigger('click');
    });

    $('#remove-item-all').on('click', function () {
        $.ajax({
            url: 'admin/carrinho/remove-all',
            type: 'post',
            contentType: false,
            processData: false,
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {

                        var time = 200;

                        $('#DataTables_Table_1_wrapper').find('tr[data-id]').each(function () {
                            var tr = $(this);
                            tr.fadeOut(time, function () {
                                tr.remove();
                            });
                            time += 50;
                        });

                        setTimeout(function () {
                            new PNotify({
                                title: 'Sucesso',
                                text: 'Seu carrinho foi esvaziado, você será direcionado aos produtos em 4 segundos.',
                                addclass: 'bg-success'
                            });
                            setTimeout(function () {
                                window.location = 'produtos';
                            }, 4000);
                        }, time);
                    }
                }
            }
        });
    });

    $('#remove-item').on('click', function () {
        var _ = $(this);
        var tr = $('tr[data-id=' + _.attr('data-id') + ']');
        var form = $('<form></form>');
        form.append('<input name="id_carrinho" type="hidden" value="' + _.attr('data-id') + '">');
        var data = gem_data(serialize(form));
        $.ajax({
            url: 'admin/carrinho/remove',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {

                        tr.fadeOut(400, function () {
                            tr.remove();

                            $('.quantidade_total').html(response.quantidade_total);
                            $('.valor_total').html('R$ ' + response.valor_total);

                            if (parseInt(response.quantidade_total) == 0) {
                                new PNotify({
                                    title: 'Sucesso',
                                    text: 'Seu carrinho ficou vazio, você será direcionado aos produtos em 4 segundos.',
                                    addclass: 'bg-success'
                                });
                                setTimeout(function () {
                                    window.location = 'produtos';
                                }, 4000);
                            } else {
                                new PNotify({
                                    title: 'Sucesso',
                                    text: 'O item foi removido e seu carrinho foi salvo com sucesso.',
                                    addclass: 'bg-success'
                                });
                            }
                        });
                    }
                }
            }
        });
    });

    var produto = new Tokens($('#produtos'), {
        url: 'admin/pedidos/produtos',
        type: 'post',
        names: ['id_produto'],
        show: 'nome',
        cantReapeatComparison: ['id_produto'],
        nothingMessage: 'Nenhum resultado encontrado.',
        remote: function (data, token) {
            token.process(data.data);
        }
    });

    $('.addnew').on('click', function () {
        var _ = $(this);

        _.html('<i class="icon-spinner11 infinite-rotation"></i>');

        var tr = _.closest('tr');
        var id = tr.attr('data-id');

        var key_to_object = tr.find('input[key-to-object]').attr('key-to-object');

        var data = gem_data(serialize($('<form></form>').append(produto.main.clone())));

        $.ajax({
            url: 'admin/carrinho/add',
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

                            $('.quantidade_total').html(response.quantidade_total);
                            $('.valor_total').html('R$ ' + response.valor_total);

                            produto.removeAll();
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
                                    $('tr[data-id=' + now.id_carrinho + '] .quantidade').val(now.quantidade);
                                    $('tr[data-id=' + now.id_carrinho + '] .valor_final').html('R$ ' + now.valor_final);
                                }
                            }

                            var antr = $('tr[data-id]').eq(0).clone();

                            for (var i in response.data.inserts) {

                                var lasttr = $('tr[data-id]');
                                lasttr = $(lasttr[lasttr.length - 1]);

                                var now = response.data.inserts[i];
                                if (now.id_carrinho == null || now.id_carrinho == false) {
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
                                    var clone = antr.clone();
                                    clone.attr('data-id', now.id_carrinho);
                                    clone.css({display: 'none'});
                                    clone.find('.quantidade').val(1);
                                    clone.find('.valor_unico').attr('value', now.valor_unico);
                                    clone.find('.valor_unico').attr('data-old', now.valor_unico);
                                    clone.find('.quantidade_estoque').text(now.quantidade_estoque);
                                    clone.find('.valor_unitario').html('R$' + now.valor_unico);
                                    clone.find('.valor_unitario_final').html('R$' + now.valor_unico);
                                    clone.find('.valor_atacado').html('R$' + now.valor_atacado);
                                    clone.find('.valor_final').html('R$ ' + now.valor_final);
                                    clone.find('td').eq(0).html(now.nome);
                                    lasttr.after(clone);
                                    rebindFields();
                                }
                            }
                            $('tr[data-id]').fadeIn(500);
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

});