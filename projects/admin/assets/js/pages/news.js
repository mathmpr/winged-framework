$(function () {

    if (typeof newsCategorias !== 'undefined') {
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
    }

    $('#News_is_video').on('change', function () {
        checkIfVideo();
    });

    $('#News_from_youtube').on('change', function () {
        checkVideoSource();
    });

    function checkIfVideo() {
        var $obj = $('#News_is_video');
        if ($obj.is(':checked')) {
            $('.is-video').fadeIn(200);
        } else {
            $('.is-video').fadeOut(200);
        }
    }

    function checkVideoSource() {
        var $obj = $('#News_from_youtube');
        var $target = $('#News_video_source');
        if ($obj.is(':checked')) {
            $target.fadeIn(200);
            $target.attr('type', 'text');
            $target.parent().parent().fadeIn(200);
            $target.parent().parent().find('label:first-child').fadeIn(200);
            $target.parent().parent().find('button').fadeOut(200);
            $target.parent().parent().find('.father-video-element').fadeOut(200);
        } else {
            $target.fadeOut(200);
            $target.attr('type', 'hidden');
            $target.parent().parent().fadeIn(200);
            $target.parent().parent().find('label:first-child').fadeOut(200);
            $target.parent().parent().find('button').fadeIn(200);
            $target.parent().parent().find('.father-video-element').fadeIn(200);
        }
    }

    checkIfVideo();
    checkVideoSource();

    selectedMidiaCallback = function () {
        checkIfVideo();
        checkVideoSource();
    };

    var cep = new FetchViaCep();

    if ($('#News_cep').length > 0) {
        cep.cep = $('#News_cep').val();
        cep.fill_mode = 'fill_null';
        cep.focus = '#News_complemento';
        cep.fill = {
            '#News_estado': function (obj) {
                $('#News_estado').val(obj.ufs[obj.lastResponse.uf]);
            },
            '#News_cidade': 'localidade',
            '#News_bairro': 'bairro',
            '#News_uf': 'uf',
            '#News_rua': 'logradouro',
            '#News_complemento': 'complemento',
            '#News_numero': false
        };
        cep.fetch();
        $('#News_cep').on('blur', function () {
            cep.cep = $('#News_cep').val();
            cep.fetch();
        });
    }

    $('#News_id_categoria').on('change', function () {
        enableCursosEventos();
    });

    function enableCursosEventos() {
        if ($('#News_id_categoria').val() === '32' || $('#News_id_categoria').val() === '33') {
            $('#cursosEventos').fadeIn(400);
        } else {
            $('#cursosEventos').fadeOut(400);
        }
    }

    enableCursosEventos();

    function rebindGaleriaDelete(){
        $('.min-height-news span').unbind('click').on('click', function(){
            var _self = $(this);
            _self.closest('.col-lg-2').fadeOut(400, function(){
                _self.closest('.col-lg-2').remove();
            })
        });
    }

    $('.galeria-news').on('click', function (event) {
        event.preventDefault();
        selectedRules.minCount = 1;
        selectedRules.maxCount = 100;
        selectedRules.types = ['image', 'video'];
        $('#midia-modal').modal('show');
        selectedMidiaCallback = function () {
            for (var i in selectedMidias) {
                var current = selectedMidias[i];
                if (current.type === 'video') {
                    var _html = '<div><video muted loop autoplay><source src="' + current.pure_url + '" type="' + current.mime_type + '"></video></div>';
                } else {
                    var _html = '<div style="background-image: url(' + current.pure_url + ')"></div>';
                }
                var html = $('<div class="mt-20 col-lg-2 col-md-2 col-sm-12 col-xs-12"><div class="min-height-news"><span class="icon-cross2"></span><input type="hidden" name="News[galeria][]" value="' + current.id + '">' + _html + '</div></div>');
                $('#newsGaleria').append(html);
            }
            rebindGaleriaDelete();
        }
    });

    rebindGaleriaDelete();

});