String.prototype.replaceAll = function (search, replacement) {
    return this.split(search).join(replacement);
};

var paymentConfiguration = {
    totalAmount: 0,
    paymentMethod: '',
    shippingCost: 0,
    store: {
        name: 'Test store',
        location: '03322001'
    },
    card: {
        number: false,
        brand: false,
        name: false,
        valid: false,
        cvv: false
    },
    plots: {
        free: 6,
        disponiblePlots: 0,
        plots: false
    },
    itens: [
        {
            itemId: '0001',
            name: 'Item one',
            itemDescription: 'A description for item 1',
            itemAmount: 20.23,
            itemQuantity: 13,
            height: 0.05,
            width: 0.2,
            length: 0.1,
            weight: 0.1
        },
        {
            itemId: '0002',
            name: 'Item two',
            itemDescription: 'A description for item 2',
            itemAmount: 5.21,
            itemQuantity: 65,
            height: 0.05,
            width: 0.05,
            length: 0.05,
            weight: 0.01
        }
    ]
};


function Correios() {
    var _this = this;
    _this.calc = function () {
        var volume = 0;
        var weight = 0;
        for (var i in paymentConfiguration.itens) {
            weight += (paymentConfiguration.itens[i].weight * 100) * paymentConfiguration.itens[i].itemQuantity;
            volume += (paymentConfiguration.itens[i].height * 100) *
                (paymentConfiguration.itens[i].width * 100) *
                (paymentConfiguration.itens[i].weight * 100) *
                paymentConfiguration.itens[i].itemQuantity;
        }
        return {
            cubicVolume: parseInt(Math.ceil(Math.cbrt(volume)) + 2),
            weight: weight / 100
        }
    }

    _this.getShippingCost = function (params, success, error) {
        console.log('http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa=&sDsSenha=&sCepOrigem=' + params.orig + '&sCepDestino=' + params.dest + '&nVlPeso=' + params.sizes.weight + '&nCdFormato=1&nVlComprimento=' + params.sizes.cubicVolume + '&nVlAltura=' + params.sizes.cubicVolume + '&nVlLargura=' + params.sizes.cubicVolume + '&sCdMaoPropria=n&nVlValorDeclarado=0&sCdAvisoRecebimento=n&nCdServico=' + params.code + '&nVlDiametro=0&StrRetorno=xml');
        $.ajax({
            url: './home/get-correios/',
            data: {
                url: 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa=&sDsSenha=&sCepOrigem=' + params.orig + '&sCepDestino=' + params.dest + '&nVlPeso=' + params.sizes.weight + '&nCdFormato=1&nVlComprimento=' + params.sizes.cubicVolume + '&nVlAltura=' + params.sizes.cubicVolume + '&nVlLargura=' + params.sizes.cubicVolume + '&sCdMaoPropria=n&nVlValorDeclarado=0&sCdAvisoRecebimento=n&nCdServico=' + params.code + '&nVlDiametro=0&StrRetorno=xml'
            },
            type: 'post',
            success: function (response) {
                try {
                    response = JSON.parse(response);
                    if (typeof success === 'function') {
                        success(response);
                    }
                } catch (e) {
                    if (typeof success === 'error') {
                        error(response);
                    }
                }
            },
            error: function (response) {
                if (typeof success === 'function') {
                    error(response);
                }
            }
        })
    }
}

function PagSeguro() {
    var _this = this;
    _this.hash = null;
    PagSeguroDirectPayment.setSessionId(PagSeguroSessionId);
    $(function () {
        $(window).on('load', function () {
            PagSeguroDirectPayment.onSenderHashReady(function (response) {
                if (response.status === 'error') {
                    console.error(response.message);
                    return false;
                }
                _this.hash = response.senderHash;
            });
        });
    });

    _this.totalAmount = function () {
        var amount = 0;
        for (var i in paymentConfiguration.itens) {
            amount += paymentConfiguration.itens[i].itemAmount * paymentConfiguration.itens[i].itemQuantity;
        }
        paymentConfiguration.totalAmount = amount;
    };

    _this.getPaymentMethods = function (success, error) {
        var amount = 0;
        for (var i in paymentConfiguration.itens) {
            amount += paymentConfiguration.itens[i].itemAmount * paymentConfiguration.itens[i].itemQuantity;
        }
        paymentConfiguration.totalAmount = amount;
        PagSeguroDirectPayment.getPaymentMethods({
            amount: amount + paymentConfiguration.shippingCost,
            success: function (response) {
                if (typeof success === 'function') {
                    success(response);
                }
            },
            error: function (response) {
                if (typeof error === 'function') {
                    error(response);
                }
            }
        });
    };

    _this.getBrand = function (success, error) {
        PagSeguroDirectPayment.getBrand({
            cardBin: paymentConfiguration.card.number,
            success: function (response) {
                if (typeof success === 'function') {
                    paymentConfiguration.card.brand = response.brand.name;
                    paymentConfiguration.card.cvvSize = response.brand.cvvSize;
                    paymentConfiguration.card.expirable = response.brand.expirable;
                    paymentConfiguration.card.international = response.brand.international;
                    paymentConfiguration.card.validationAlgorithm = response.brand.validationAlgorithm;
                    success(response);
                }
            },
            error: function (response) {
                if (typeof error === 'function') {
                    error(response);
                }
            }
        });
    };

    _this.createCardToken = function (success, error) {
        PagSeguroDirectPayment.createCardToken({
            cardNumber: paymentConfiguration.card.number,
            brand: paymentConfiguration.card.brand,
            cvv: paymentConfiguration.card.cvv,
            expirationMonth: paymentConfiguration.card.valid.split('/')[0],
            expirationYear: paymentConfiguration.card.valid.split('/')[1],
            success: function (response) {
                if (typeof success === 'function') {
                    paymentConfiguration.card.token = response.card.token;
                    success(response);
                }
            },
            error: function (response) {
                if (typeof error === 'function') {
                    error(response);
                }
            }
        });
    };

    _this.getPlots = function (success, error) {
        if (paymentConfiguration.totalAmount === 0) {
            var amount = 0;
            for (var i in paymentConfiguration.itens) {
                amount += paymentConfiguration.itens[i].itemAmount * paymentConfiguration.itens[i].itemQuantity;
            }
            paymentConfiguration.totalAmount = amount;
        }
        PagSeguroDirectPayment.getInstallments({
            amount: (paymentConfiguration.totalAmount + paymentConfiguration.shippingCost),
            maxInstallmentNoInterest: paymentConfiguration.plots.free,
            brand: paymentConfiguration.card.brand,
            success: function (response) {
                if (typeof success === 'function') {
                    paymentConfiguration.plots.plots = response.installments[paymentConfiguration.card.brand];
                    success(response);
                }
            },
            error: function (response) {
                if (typeof error === 'function') {
                    error(response);
                }
            }
        });
    }
};

$(function () {

    var pagseguro = new PagSeguro();
    var correios = new Correios();

    pagseguro.totalAmount();

    var amount = 0;
    var current_amount = 0;

    for (var i in paymentConfiguration.itens) {
        amount += paymentConfiguration.itens[i].itemAmount * paymentConfiguration.itens[i].itemQuantity;
        current_amount = paymentConfiguration.itens[i].itemAmount * paymentConfiguration.itens[i].itemQuantity;
        $('#cart').find('tbody').append('<tr>' +
            '<th scope="row">' + (i + 1) + '</th>' +
            '<td>' + paymentConfiguration.itens[i].name + '</td>' +
            '<td>' + paymentConfiguration.itens[i].itemAmount + '</td>' +
            '<td>' + paymentConfiguration.itens[i].itemQuantity + '</td>' +
            '<td>' + current_amount + '</td>' +
            '<input type="hidden" name="itemId' + (parseInt(i) + 1) + '" value="' + paymentConfiguration.itens[i].itemId + '"/>' +
            '<input type="hidden" name="itemDescription' + (parseInt(i) + 1) + '" value="' + paymentConfiguration.itens[i].itemDescription + '"/>' +
            '<input type="hidden" name="itemAmount' + (parseInt(i) + 1) + '" value="' + paymentConfiguration.itens[i].itemAmount + '"/>' +
            '<input type="hidden" name="itemQuantity' + (parseInt(i) + 1) + '" value="' + paymentConfiguration.itens[i].itemQuantity + '"/>' +
            '</tr>');
    }

    $('#cart').find('tbody').append('<tr>' +
        '<td colspan="3"></td>' +
        '<td>Valor total dos produtos + frete:</td>' +
        '<td id="totalCost">' + amount + '</td>' +
        '</tr>');

    $('#billingAddressPostalCode').on('blur', function () {
        var self = $(this);
        if (self.val() !== '' && self.val().length === 10) {
            $.ajax({
                url: './home/get-cep/',
                type: 'post',
                data: {
                    cep: self.val()
                },
                success: function (response) {
                    try {
                        response = $.parseJSON(response);
                        $('#billingAddressStreet').val(response.data.rua);
                        $('#billingAddressState').val(response.data.estado);
                        $('#billingAddressCity').val(response.data.cidade);
                        $('#billingAddressDistrict').val(response.data.bairro);
                        $('#billingAddressNumber').focus();
                    } catch (e) {
                        return e;
                    }
                }
            });
        }
    });

    $('#shippingAddressRequired').on('click', function () {
        if (this.checked) {
            $('#useSameLocation').closest('.none').css({display: 'block'});
            $('#sameLocation').css({display: 'block'});
        } else {
            $('#useSameLocation').closest('.none').css({display: 'none'});
            $('#sameLocation').css({display: 'none'});
        }
    });

    $('#useSameLocation').on('click', function () {
        if (this.checked) {
            $('#shippingAddressPostalCode').val($('#billingAddressPostalCode').val());
            $('#shippingAddressStreet').val($('#billingAddressStreet').val());
            $('#shippingAddressNumber').val($('#billingAddressNumber').val());
            $('#shippingAddressState').val($('#billingAddressState').val());
            $('#shippingAddressCity').val($('#billingAddressCity').val());
            $('#shippingAddressDistrict').val($('#billingAddressDistrict').val());
            $('#shippingAddressComplement').val($('#billingAddressComplement').val());
        } else {
            $('#shippingAddressPostalCode').val('');
            $('#shippingAddressStreet').val('');
            $('#shippingAddressNumber').val('');
            $('#shippingAddressState').val('');
            $('#shippingAddressCity').val('');
            $('#shippingAddressDistrict').val('');
            $('#shippingAddressComplement').val('');
        }
    });

    $('#calcFrete').on('click', function () {
        if ($('#shippingAddressPostalCode').val() != '' && $('#shippingAddressPostalCode').val().length === 10) {
            var services = [
                '',
                '41106', //PAC
                '40010', //SEDEX
                '40045', //SEDEX
                '40215'  //SEDEX 10
            ];
            var sizes = correios.calc();
            correios.getShippingCost({
                sizes: sizes,
                orig: paymentConfiguration.store.location,
                dest: $('#shippingAddressPostalCode').val().replaceAll('.', '').replaceAll('-', ''),
                code: services[parseInt($('input[name=shippingType]:checked').val())]
            }, function (response) {
                $('#shippingCost').val(response.data.cServico.Valor.replaceAll(',', '.'));
                $('#remainingDays').html('Chega em ' + response.data.cServico.PrazoEntrega + ' dia(s)');
                paymentConfiguration.shippingCost = parseFloat(response.data.cServico.Valor.replaceAll(',', '.'));
                $('#totalCost').html(paymentConfiguration.totalAmount + paymentConfiguration.shippingCost);


                pagseguro.getPaymentMethods(function (response) {
                    paymentConfiguration.paymentMethods = response.paymentMethods;
                    $('#methods').closest('.none').css({display: 'block'});
                    $('#methods').html('');
                    for (var i in response.paymentMethods) {
                        var lower = i.toLowerCase();
                        var refes = {
                            'BOLETO': 'Boleto',
                            'BALANCE': 'Saldo PagSeguro',
                            'ONLINE_DEBIT': 'Debito online',
                            'CREDIT_CARD': 'Cartão de crédito',
                            'DEPOSIT': 'Deposito'
                        };
                        $('#methods').append('<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">' +
                            '<label for="payment_' + lower + '"><input value="' + lower + '" id="payment_' + lower + '" type="radio" name="paymentMethod"><span></span><span>' + refes[i] + '</span></label></div>');

                    }

                    $('input[name=paymentMethod]').unbind('click').on('click', function () {
                        var none = $('input[name=paymentMethod]').closest('.row.none');
                        $('.row.none').not(none).css({
                            display: 'none'
                        });
                        $('div[data-active="' + $('input[name=paymentMethod]:checked').val() + '"]').css({
                            display: 'flex'
                        });
                    });

                    if ('CREDIT_CARD' in response.paymentMethods) {
                        $('#brands').html();
                        for (var i in response.paymentMethods.CREDIT_CARD.options) {
                            $('#brands').append('<li><img src="./Modules/PagSeguro/Assets/images/' + i.toLowerCase() + '.png" alt="' + response.paymentMethods.CREDIT_CARD.options[i].displayName + '"/></li>')
                        }
                    }

                    var current_brand = false;
                    var current_total_amount = false;

                    $('#creditCardNumber, #creditCardHolderName, #creditCardCVV, #creditCardValid').on('blur keyup', function () {

                        $('#senderHash').val(pagseguro.hash);

                        paymentConfiguration.card.cvv = $('#creditCardCVV').val();
                        paymentConfiguration.card.number = $('#creditCardNumber').val().replaceAll('.', '');
                        paymentConfiguration.card.name = $('#creditCardHolderName').val();
                        paymentConfiguration.card.valid = $('#creditCardValid').val();

                        pagseguro.getBrand(function () {
                            $('#creditCardNumber').closest('.form-group').find('img').remove();
                            $('#creditCardNumber').closest('.form-group').prepend('<img src="./Modules/PagSeguro/Assets/images/' + paymentConfiguration.card.brand + '.png" alt="' + paymentConfiguration.card.brand + '"/>');
                            if (paymentConfiguration.card.number &&
                                paymentConfiguration.card.brand &&
                                paymentConfiguration.card.name &&
                                paymentConfiguration.card.valid &&
                                paymentConfiguration.card.cvv) {
                                pagseguro.createCardToken(function (response) {
                                    $('#creditCardToken').val(response.card.token);
                                });
                                if (paymentConfiguration.card.brand != current_brand && paymentConfiguration.totalAmount != current_total_amount) {
                                    current_brand = paymentConfiguration.card.brand;
                                    current_total_amount = paymentConfiguration.totalAmount + paymentConfiguration.shippingCost;
                                    $('#noInterestInstallmentQuantity').val(paymentConfiguration.plots.free);
                                    pagseguro.getPlots(function (response) {
                                        $('#installmentQuantity, #installmentValue').html('');
                                        for (var i in response.installments[paymentConfiguration.card.brand]) {
                                            $('#installmentValue').append('<option value="' + response.installments[paymentConfiguration.card.brand][i].installmentAmount + '">' +
                                                '' + response.installments[paymentConfiguration.card.brand][i].quantity + 'x de ' + response.installments[paymentConfiguration.card.brand][i].installmentAmount + ' = ' + response.installments[paymentConfiguration.card.brand][i].totalAmount + '</option>');
                                            $('#installmentQuantity').append('<option value="' + response.installments[paymentConfiguration.card.brand][i].quantity + '">.</option>');
                                        }
                                        $('#installmentValue').closest('.form-group').parent().css({
                                            display: 'block'
                                        });
                                    });
                                    $('#installmentValue').unbind('change').on('change', function () {
                                        var self = $(this);
                                        $('#installmentQuantity').find('option').removeAttr('selected');
                                        $('#installmentQuantity').find('option').eq(self.find('option:selected')[0].index).attr('selected', 'selected');
                                    });
                                }
                            }
                        });
                    });

                });


            }, function (response) {

            });
        }
    });

});





