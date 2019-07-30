var minContainerHeight = 350;
var minContainerWidth = false;

var selectedMidias = [];

var selectedMidiaCallback = false;

var selectedRules = {
    'minCount': 1,
    'maxCount': 100,
    'types': ['pdf', 'doc', 'sound', 'video', 'image', 'zip', 'xls'],
};

$(function () {
    $.alpaca.setDefaultLocale("pt_BR");

    $.uniform.defaults.fileButtonHtml = 'Escolha um arquivo';
    $.uniform.defaults.fileDefaultHtml = 'Nenhum arquivo selecionado';

    $(".uniform-uploader").uniform({
        fileButtonClass: 'action btn bg-blue'
    });

    var exists = [];

    $('.midia-info').each(function () {
        var _self = $(this);
        if (_self.val() !== '') {
            exists.push(_self.closest('div').find('input').val());
        } else {
            exists.push(0);
        }
    });

    $.ajax({
        url: window.protocol + window._parent + 'services/get-midia-information',
        type: 'post',
        data: {
            'in': exists
        },
        success: function (response) {
            response = json(response);
            if (response) {
                if (response.status) {
                    exists = response.data;
                    for (var i in exists) {
                        var midia = exists[i];
                        var div = $('.midia-info[value="' + midia.id + '"]').parent().parent();
                        div.find('.father-image-element').remove();
                        div.find('.father-video-element').remove();
                        div.append(midia.html);
                        var appended = div.find('.father-image-element');
                        if (appended.length === 0) {
                            appended = div.find('.father-video-element');
                            appended.prepend('<div class="file-info"><p class="help-block">Tamanho do arquivo: ' + midia.size + '</p></div>');
                        } else {
                            appended.prepend('<div class="file-info"><p class="help-block">Proporção: ' + midia.proportion + '</p><p class="help-block">Tamanho do arquivo: ' + midia.size + '</p></div>');
                        }
                    }
                    if (is_function(selectedMidiaCallback)) {
                        selectedMidiaCallback();
                    }
                }
            }
        }
    });

    $('.open-midia-image-one').unbind('click').on('click', function (event) {
        event.preventDefault();
        var _self = $(this);
        selectedRules.maxCount = 1;
        selectedRules.types = ['image'];
        $('#open-midia').trigger('click');
        selectedMidiaCallback = function () {
            if (selectedMidias.length === 0) {
                raisePerror('Você não selecionanou nenhuma imagem.');
                return;
            }
            var midia = selectedMidias[0];
            _self.closest('div').find('input').val(midia.id);
            _self.closest('div').find('.father-image-element').remove();
            _self.closest('div').find('.father-video-element').remove();
            _self.closest('div').append(midia.html);
            var appended = _self.closest('div').find('.father-image-element');
            if (appended.length === 0) {
                appended = _self.closest('div').find('.father-video-element');
                appended.prepend('<div class="file-info"><p class="help-block">Tamanho do arquivo: ' + midia.size + '</p></div>');
            } else {
                appended.prepend('<div class="file-info"><p class="help-block">Proporção: ' + midia.proportion + '</p><p class="help-block">Tamanho do arquivo: ' + midia.size + '</p></div>');
            }
        }
    });

    $('.open-midia-video-one').unbind('click').on('click', function (event) {
        event.preventDefault();
        var _self = $(this);
        selectedRules.maxCount = 1;
        selectedRules.types = ['video'];
        $('#open-midia').trigger('click');
        selectedMidiaCallback = function () {
            if (selectedMidias.length === 0) {
                raisePerror('Você não selecionanou nenhum vídeo.');
                return;
            }
            var midia = selectedMidias[0];
            _self.closest('div').find('input').val(midia.id);
            _self.closest('div').find('.father-vedeo-element').remove();
            _self.closest('div').append(midia.html);
            var appended = _self.closest('div').find('.father-video-element');
            appended.prepend('<div class="file-info"><p class="help-block">Tamanho do arquivo: ' + midia.size + '</p></div>');
        }
    });

    $('#add-selected').on('click', function (event) {
        event.preventDefault();
        $('#midia-modal').modal('hide');
        selectedRules = {
            'minCount': 1,
            'maxCount': 100,
            'types': ['pdf', 'doc', 'sound', 'video', 'image', 'zip', 'xls'],
        };
        var mainModal = $('#midia-modal');
        mainModal.find('.selected').each(function () {
            selectedMidias.push($(this).attr('data-id'));
        });
        if (selectedMidias.length > 0) {
            $.ajax({
                url: window.protocol + window._parent + 'services/get-midia-information',
                type: 'post',
                data: {
                    'in': selectedMidias,
                },
                success: function (response) {
                    response = json(response);
                    if (response) {
                        if (response.status) {
                            raisePsuccess('Mídia(s) adicionada(s) com sucesso.')
                            if (is_function(selectedMidiaCallback)) {
                                selectedMidias = response.data;
                                selectedMidiaCallback();
                            }
                        } else {
                            raisePerror('Pode ser que a(s) mídia(s) selcionada(s) tenham sidas deletadas por outro usuário.')
                        }
                    } else {
                        raisePerror('Não foi possível selecionar a(s) mídia(s) agora. Tente novamente mais tarde.');
                    }
                }
            });
        } else {
            if (is_function(selectedMidiaCallback)) {
                selectedMidiaCallback();
            }
        }
    });

    $('#midia-modal').on('show.bs.modal', function () {
        selectedMidias = [];
        var mainModal = $('#midia-modal');
        mainModal.find('.selected').removeClass('selected');
        unbindBindMidiaModal();
    });

    $('.modal').on('hidden.bs.modal', function () {
        checkModalWasOpen();
    });

    $('*[data-close-modal]').on('click', function (event) {
        event.preventDefault();
        $($(this).attr('data-close-modal')).modal('hide');
        checkModalWasOpen();
    });

    $('#render-delete-midia').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var mainModal = $('#midia-modal');
        var _self = $(this);
        if (_self.attr('data-id') != null && _self.attr('data-id') !== '') {
            $.ajax({
                url: window.protocol + window._parent + 'services/delete-midia',
                type: 'post',
                data: {
                    id: _self.attr('data-id')
                },
                success: function (response) {
                    response = json(response);
                    if (response) {
                        if (response.status) {
                            if (mainModal.find('.info').attr('data-id') === _self.attr('data-id')) {
                                mainModal.find('.info').html('<span class="help-block">Nenhum arquivo selecionado.</span>');
                            }
                            var col = mainModal.find('.type[data-id=' + _self.attr('data-id') + ']').closest('.col-lg-3');
                            $('#delete-midia-modal').modal('hide');
                            checkModalWasOpen();
                            col.fadeOut(400, function () {
                                col.remove();
                            });
                        }
                    }
                }
            });
        }
    });

    function checkModalWasOpen() {
        setTimeout(function () {
            $('div.modal').each(function () {
                if ($(this).is(':visible')) {
                    $('body').addClass('modal-open');
                }
            });
        }, 400);
    }

    function fetchMoreMidia(from, to, callback, append) {
        var mainModal = $('#midia-modal');
        from = from || 0;
        to = to || 10;
        callback = callback || false;
        append = append || false;
        $.ajax({
            url: window.protocol + window._parent + 'services/get-midia',
            type: 'post',
            data: {
                from: from,
                to: to
            },
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {
                        for (var _i in response.data) {
                            var current = response.data[_i];
                            if (append) {
                                mainModal.find('.loads').append(current.html);
                            } else {
                                mainModal.find('.loads').prepend(current.html);
                            }
                            unbindBindMidiaControls();
                        }
                        if (response.have_more) {
                            mainModal.find('.load-more').attr('data-from', parseInt(mainModal.find('.load-more').attr('data-from')) + 10);
                        } else {
                            mainModal.find('.load-more').addClass('disabled');
                        }
                        if (is_function(callback)) {
                            callback();
                        }
                    }
                }
            }
        });
    }

    function unbindBindMidiaControls() {

        var mainModal = $('#midia-modal');

        var strings = {
            'pdf': 'documentos PDF',
            'doc': 'documentos do Word',
            'sound': 'arquivos de música',
            'video': 'arquivos de vídeos',
            'image': 'arquivos de imagem',
            'zip': 'arquivos compactados',
            'xls': 'arquivos do Excel ou Planilhas'
        };

        mainModal.find('.type').unbind('click').on('click', function () {
            var _self = $(this);
            if (_self.hasClass('selected')) {
                _self.removeClass('selected');
            } else {
                if (mainModal.find('.type.selected').length >= selectedRules.maxCount) {
                    raisePerror('Você não pode selecionar mais que ' + selectedRules.maxCount + ' mídia(s).');
                } else {
                    var $type = _self.attr('class').replace('icon', '').replace('type', '').trim();
                    if (selectedRules.types.indexOf($type) < 0) {
                        raisePerror('Você não pode selecionar ' + strings[$type] + '. O objeto que chamou mídias não permite o tipo.');
                    } else {
                        _self.addClass('selected');
                    }
                }
            }
        });

        mainModal.find('.loads .delete').unbind('click').on('click', function (event) {
            event.preventDefault();
            $('#delete-midia-modal').modal('show');
            $('#render-delete-midia').attr('data-id', $(this).attr('data-id'));
        });
        mainModal.find('.loads .update').unbind('click').on('click', function (event) {
            event.preventDefault();
            var _self = $(this);
            if (_self.attr('data-id') != null && _self.attr('data-id') !== '') {
                var errorMessage = 'Não foi possível abrir as informações da mídia. Tente novamente mais tarde e se o erro persistir, entre em contato com o desenvolvedor.';
                $.ajax({
                    url: window.protocol + window._parent + 'services/get-edit-form-midia',
                    type: 'post',
                    data: {
                        id: _self.attr('data-id')
                    },
                    success: function (response) {
                        response = json(response);
                        if (response) {
                            if (response.status) {
                                mainModal.find('.info').attr('data-id', _self.attr('data-id'));
                                mainModal.find('.info').html(response.html);
                                $('#update-midia-form .make-update').on('click', function (event) {
                                    event.preventDefault();
                                    var errorMessage = 'Não foi possível salvar a mídia. Tente novamente mais tarde e se o erro persistir, entre em contato com o desenvolvedor.';
                                    $.ajax({
                                        url: window.protocol + window._parent + 'services/update-midia',
                                        type: 'post',
                                        data: $('#update-midia-form').serialize(),
                                        success: function (response) {
                                            response = json(response);
                                            if (response) {
                                                if (response.status) {
                                                    raisePsuccess('Mídia salva com sucesso.');
                                                } else {
                                                    raisePerror(errorMessage);
                                                }
                                            } else {
                                                raisePerror(errorMessage);
                                            }
                                        },
                                        error: function () {
                                            raisePerror(errorMessage);
                                        }
                                    });
                                });

                                $('#update-midia-form .replace').on('click', function (event) {
                                    event.preventDefault();
                                    $('.html5-uploader').remove();
                                    mainModal.find('.modal-body').prepend('<div class="html5-uploader"></div>');
                                    resetPlupload();
                                    setTimeout(function () {
                                        var uploader = $('.html5-uploader').pluploadQueue();
                                        uploader.setOption('multipart_params', {
                                            "id": $('#Midias_id').val()
                                        });
                                        uploader.bind('FilesAdded', function (uploader) {
                                            uploader.start();
                                        });
                                        uploader.settings.browse_button[0].click();
                                    }, 800);
                                });

                            } else {
                                raisePerror(errorMessage);
                            }
                        } else {
                            raisePerror(errorMessage);
                        }
                    },
                    error: function () {
                        raisePerror(errorMessage);
                    }
                });
            }
        });

        mainModal.find('.loads .update-image').unbind('click').on('click', function (event) {
            event.preventDefault();
            var _self = $(this);
            $('#the-cropper .jcrop-result').attr('style', 'background-image: url(' + _self.attr('data-pure-url') + '?get=' + rand(1, 1000) + ')');
            $('#the-cropper .jcrop-result').html('<div class="image-cropper-container"></div>')
            $('#render-update-midia').attr('data-id', _self.attr('data-id'));
            $('#midia-cropper').modal('show');
            minContainerHeight = _self.attr('data-height');
            minContainerWidth = _self.attr('data-width');
        });
    }

    $('#midia-cropper').on('shown.bs.modal', function () {
        initCropper();
    });

    function initCropper() {

        var mainModal = $('#midia-modal');
        var midiaModal = $('#midia-cropper');

        minContainerWidth = parseInt(minContainerWidth);
        minContainerHeight = parseInt(minContainerHeight);

        var minWidth = (($("#the-cropper").width() < minContainerWidth) ? $("#the-cropper").width() : minContainerWidth);
        if (minWidth !== parseInt(minContainerWidth)) {
            var diff = parseInt(minContainerWidth) - minWidth;
            var per = diff * 100 / minContainerWidth;
            minContainerHeight = minContainerHeight - (minContainerHeight * per / 100);
        }

        $('#the-cropper .jcrop-result').width(minWidth);
        $('#the-cropper .jcrop-result').height(minContainerHeight);

        $('#the-cropper .image-cropper-container').width(minWidth);
        $('#the-cropper .image-cropper-container').height(minContainerHeight);

        var cropper = $('#the-cropper .image-cropper-container');
        var jcrop_api;
        cropper.Jcrop({
            bgFade: true,
            //onRelease: releaseCheck
        }, function () {

            jcrop_api = this;
            jcrop_api.animateTo([100, 100, 400, 300]);

        });

        midiaModal.find('.aspect').unbind('change').on('change', function () {
            midiaModal.find('.aspect').each(function () {
                var _self = $(this);
                if (_self.is(':checked')) {
                    cropper.cropper('setAspectRatio', _self.val());
                    setTimeout(function () {
                        console.log(cropper.cropper('getCanvasData'));
                    }, 400);
                }
            });
        });

        midiaModal.find('.sizes').unbind('change').on('change', function () {
            setTimeout(function () {
                midiaModal.find('.sizes').parent().removeClass('acitve');
                midiaModal.find('.sizes').each(function () {
                    var _self = $(this);
                    var sizes = _self.val().split('x');
                    sizes[0] = parseInt(sizes[0]);
                    sizes[1] = parseInt(sizes[1]);
                    var _self = $(this);
                    if (_self.is(':checked')) {
                        _self.parent().addClass('acitve');
                        cropper.cropper('setAspectRatio', (sizes[0] / sizes[1]));
                        setTimeout(function () {
                            console.log(cropper.cropper('getCanvasData'));
                        }, 400);
                    }
                });
            }, 150);
        });

        var currentZoom = 1;

        midiaModal.find('.add').on('click', function (event) {
            event.preventDefault();
            if (currentZoom <= 1.95) {
                currentZoom += 0.05;
            }
            controllZoom();
        });

        midiaModal.find('.minus').on('click', function (event) {
            event.preventDefault();
            if (currentZoom >= 0.1) {
                currentZoom -= 0.05;
            }
            controllZoom();
        });

        function controllZoom() {
            var containerData = cropper.cropper('getContainerData');
            console.log(containerData)
            cropper.cropper('zoomTo', currentZoom, {
                x: containerData.width / 2,
                y: containerData.height / 2
            });
        }

    }

    function resetPlupload() {
        var mainModal = $('#midia-modal');
        $('.html5-uploader').pluploadQueue({
            runtimes: 'html5',
            url: window.protocol + window._parent + 'services/upload',
            unique_names: true,
            filters: {
                mime_types: [{
                    extensions: 'jpg,gif,png,jpeg,tiff,mp4,avi,pdf,docx,doc,csv,xls,xslx,mpeg,ogg,mp3,wav,zip,rar'
                }]
            }
        });

        var uploader = $('.html5-uploader').pluploadQueue();
        uploader.bind('FileUploaded', function (internalUploader, file, result) {
            result = json(result.response);
            if (result) {
                if (result.status) {
                    if (result.update) {
                        var htmls = [];
                        for (var _i in result.data) {
                            var col = mainModal.find('.type[data-id=' + result.update + ']').closest('.col-lg-3');
                            var current = result.data[_i];
                            var html = $(current.html);
                            htmls.push(html);
                            col.replaceWith(html);
                        }
                        unbindBindMidiaControls();
                        setTimeout(function () {
                            for (var i in htmls) {
                                htmls[i].find('.update').trigger('click');
                            }
                        }, 200);

                    } else {
                        for (var _i in result.data) {
                            var current = result.data[_i];
                            mainModal.find('.loads').prepend(current.html);
                        }
                        unbindBindMidiaControls();
                        if ((mainModal.find('.loads .col-lg-3').length + 10) > parseInt(mainModal.find('.load-more').attr('data-from'))) {
                            mainModal.find('.load-more').attr('data-from', (mainModal.find('.loads .col-lg-3').length + 10));
                        }
                    }
                }
            }
        });

        uploader.bind('UploadComplete', function () {
            $('.plupload_filelist_footer .plupload_upload_status').css({display: 'inline-block'});
            $('.plupload_filelist_footer .plupload_file_name').prepend('<button id="renew-plupload" class="plupload_button plupload_add mr-20">Enviar mais arquivos</button>');
            setTimeout(function () {
                $('#renew-plupload').on('click', function () {
                    $('.html5-uploader').remove();
                    mainModal.find('.modal-body').prepend('<div class="html5-uploader"></div>');
                    resetPlupload();
                });
            }, 100);
        });
    }

    function unbindBindMidiaModal() {
        var mainModal = $('#midia-modal');
        if (!mainModal.hasClass('initialized')) {
            mainModal.addClass('initialized');
            fetchMoreMidia();
            resetPlupload();
        }
        mainModal.find('.load-more').unbind('click').on('click', function (event) {
            event.preventDefault();
            if (!$(this).hasClass('disabled')) {
                fetchMoreMidia((parseInt($(this).attr('data-from')) - 10), parseInt($(this).attr('data-from')), false, true);
            }
        })
    }

    function unbindBindSlugs() {
        $('*[data-slug]').each(function () {
            activeSlug($(this));
        });

        $('*[data-slug]').unbind('keyup').on('keyup', function () {
            activeSlug($(this));
        });

        $('*[data-slug]').unbind('blur').on('blur', function () {
            activeSlug($(this));
        });

        $('.core-slugify').unbind('keyup').on('keyup', function () {
            if ($(this).val() === '') {
                $(this).removeClass('cant-edit');
            } else {
                $(this).addClass('cant-edit');
            }
        });

        $('.core-slugify').unbind('blur').on('blur', function () {
            $($(this)).val(slugify($(this).val()).substr(0, 60));
            validateSlug($(this));
        });

        $('*[data-slug]').unbind('blur').on('blur', function () {
            if ($(this).attr('data-slug').trim() !== '') {
                if ($($(this).attr('data-slug')).length > 0) {
                    validateSlug($($(this).attr('data-slug')));
                }
            }
        });
    }

    unbindBindSlugs();

    function validateSlug(slugInput) {
        var label = slugInput.parent().next();
        $.ajax({
            url: window.protocol + window._parent + 'services/validate-slug',
            type: 'post',
            data: {
                slug: slugInput.val(),
                linkTo: slugInput.attr('data-linkTo'),
                tableName: slugInput.attr('data-tableName')
            },
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {
                        label.removeClass();
                        label.addClass('validation-invalid-label').addClass('validation-valid-label');
                        label.fadeIn(200);
                        label.html('Este nome está diponível para URL.')
                    } else {
                        label.removeClass();
                        label.addClass('error').addClass('validation-error-label');
                        label.fadeIn(200);
                        label.html(response.errors.slug.select);
                        slugInput.removeClass('cant-edit');
                    }
                }
            }
        });
    }

    function activeSlug(jQueryObject) {
        if (jQueryObject.attr('data-slug').trim() !== '') {
            if ($(jQueryObject.attr('data-slug')).length > 0) {
                if (!$(jQueryObject.attr('data-slug')).hasClass('cant-edit')) {
                    $(jQueryObject.attr('data-slug')).val(slugify(jQueryObject.val()).substr(0, 60));
                }
            }
        }
    }

    var editor = $('.summernote');
    editor.each(function () {
        var _self = $(this);
        _self.summernote({
            height: 500,
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['insert', ['link', 'hr']],
                ['misc', ['fullscreen', 'codeview', 'undo', 'redo']]
            ],
            styleTags: [
                {title: 'Citação', tag: 'blockquote', className: 'blockquote', value: 'blockquote'},
                {title: 'Título', tag: 'h4', className: 'bigtitle', value: 'h3'},
                {title: 'Paragráfo', tag: 'p', className: 'striked', value: 'p'}
            ],
            lang: "pt-BR",
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    document.execCommand('insertText', false, bufferText);
                    setTimeout(function () {
                        $(".summernote").summernote("code", $(".summernote").summernote("code")
                            .replaceAll("\n", "")
                            .replaceAll('<p/><p><br></p><p>', '')
                            .replaceAll('<p/><p><br/></p><p>', '')
                            .replaceAll('<p/><p></p><p>', ''));
                    }, 200);
                }
            }
        });
    });

    var currentPressButton = null;
    var editorMidia = $('.summernote.perm-midias');
    editorMidia.each(function () {
        var _self = $(this);
        _self.parent().before('<p class="mt-10"><button class="summernote-midia-modal btn bg-primary-400 mb-20">Adicionar mídia ao corpo do post</button></p>');
        _self.on('summernote.blur', function(){
            _self.summernote('saveRange');
        });

        _self.on('summernote.focus', function(){
            _self.summernote('retoreRange');
        });

    });

    $('.summernote-midia-modal').on('click', function (event) {
        currentPressButton = $(this);
        event.preventDefault();
        $('#midia-modal').modal('show');
        selectedRules = {
            'minCount': 1,
            'maxCount': 1,
            'types': ['sound', 'video', 'image'],
        };
        selectedMidiaCallback = function () {
            var $fG = currentPressButton.closest('.form-group');
            if (selectedMidias.length > 0) {
                var current = selectedMidias[0];
                var html = $('<p>' + current.html + '</p>');
                var summernote = $fG.find('.summernote');
                summernote.summernote('focus');
                setTimeout(function () {
                    summernote.summernote('pasteHTML', html);
                }, 500);
            } else {
                raisePwarning('Você não selecionou nenhuma mídia.')
            }
        }
    });

    $(document).on('change', '.uniform-uploader', function () {
        var $input = $(this),
            filenames = $.map($input[0].files, function (file) {
                return file.name;
            });
        $input.siblings('.filename').html(filenames.join(', '));
    });

    var sort_start = 0;
    var sort_end = 0;
    if (jQuery().sortable) {
        $('.sortable_tr').sortable({

            start: function (event, ui) {
                var x = 0;
                var increment = true;
                $('.sortable_tr tr').each(function () {
                    if ($(this).is(ui.item)) {
                        increment = false;
                    }
                    if (increment) {
                        x++;
                    }
                });
                sort_start = x;
            },

            stop: function (event, ui) {
                var x = 0;
                var increment = true;
                $('.sortable_tr tr').each(function () {
                    if ($(this).is(ui.item)) {
                        increment = false;
                    }
                    if (increment) {
                        x++;
                    }
                });
                sort_end = x;
                var walk = 0;
                if (sort_start > sort_end) {
                    $($('.sortable_tr tr')[sort_end]).attr('data-ordem', $($('.sortable_tr tr')[sort_end + 1]).attr('data-ordem'));
                    walk = sort_end + 1;
                    while (walk <= sort_start) {
                        var ele = $($('.sortable_tr tr')[walk]);
                        ele.attr('data-ordem', parseInt(ele.attr('data-ordem')) + 1);
                        walk++;
                    }
                } else if (sort_start < sort_end) {
                    $($('.sortable_tr tr')[sort_end]).attr('data-ordem', $($('.sortable_tr tr')[sort_end - 1]).attr('data-ordem'));
                    walk = sort_end - 1;
                    while (walk >= sort_start) {
                        var ele = $($('.sortable_tr tr')[walk]);
                        ele.attr('data-ordem', parseInt(ele.attr('data-ordem')) - 1);
                        walk--;
                    }
                }

                var form = $('<form></form>');
                $('.sortable_tr tr').each(function () {
                    form.append('<input name="id[]" value="' + $(this).attr('data-id') + '">');
                    form.append('<input name="ordem[]" value="' + $(this).attr('data-ordem') + '">');
                });

                var data = gem_data(serialize(form));
                $.ajax({
                    url: window.protocol + window._parent + window.page_surname + '/change-position',
                    type: 'post',
                    data: data,
                    async: false,
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }
        });
        $('.sortable_tr').disableSelection();
    }


    $('.datepicker').mask('99/99/9999 99:99:99').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
        autoUpdateInput: true,
        locale: {
            format: 'DD/MM/YYYY hh:mm:ss'
        }
    });

});

function FetchViaCep(cep, fill, fill_mode, focus) {
    var _this = this;
    _this.cep = cep || '01001000';
    _this.fill = fill || {};
    _this.focus = focus || false;
    _this.fill_mode = fill_mode || 'fill_all';
    _this.lastResponse = {};

    _this.ufs = {
        AC: 'Acre',
        AL: 'Alagoas',
        AP: 'Amapá',
        AM: 'Amazonas',
        BA: 'Bahia',
        CE: 'Ceará',
        ES: 'Espírito Santo',
        GO: 'Goiás',
        MA: 'Maranhão',
        MT: 'Mato Grosso',
        MS: 'Mato Grosso do Sul',
        MG: 'Minas Gerais',
        PA: 'Pará',
        PB: 'Paraíba',
        PR: 'Paraná',
        PE: 'Pernambuco',
        PI: 'Piauí',
        RJ: 'Rio de Janeiro',
        RN: 'Rio Grande do Norte',
        RS: 'Rio Grande do Sul',
        RO: 'Rondônia',
        RR: 'Roraima',
        SC: 'Santa Catarina',
        SP: 'São Paulo',
        SE: 'Sergipe',
        TO: 'Tocantins',
        DF: 'Distrito Federal'
    };

    _this.isAlphaNumeric = function (str) {
        var code, i, len;

        for (i = 0, len = str.length; i < len; i++) {
            code = str.charCodeAt(i);
            if (!(code > 47 && code < 58) &&
                !(code > 64 && code < 91) &&
                !(code > 96 && code < 123)) {
                return false;
            }
        }
        return true;
    };

    _this.parseResponse = function (_json) {
        if (is_string(_json)) {
            _json = json(_json);
        }
        if (_json) {
            console.log(_json);
            _this.lastResponse = _json;
            for (var i in _this.fill) {
                if (is_function(_this.fill[i])) {
                    if (_this.fill_mode === 'fill_all') {
                        _this.fill[i](_this)
                    } else if ($(i).val().trim() === '') {
                        _this.fill[i](_this)
                    }
                } else {
                    if (_this.fill_mode === 'fill_all') {
                        $(i).val(_json[_this.fill[i]]);
                    } else if ($(i).val().trim() === '') {
                        $(i).val(_json[_this.fill[i]]);
                    }
                }
            }
            if (_this.focus && $(_this.focus).length > 0) {
                $(_this.focus).focus();
            }
        }
        for (var i in _this.fill) {
            $(i).removeAttr('readonly');
        }
    };

    _this.fetch = function () {
        _this.cep = _this.cep.replace('-', '').replace('.', '').trim();
        if (_this.cep.length === 8 && _this.isAlphaNumeric(_this.cep)) {
            $.ajax({
                url: 'https://viacep.com.br/ws/' + _this.cep + '/json/',
                type: 'get',
                success: function (response) {
                    _this.parseResponse(response)
                },
                error: function () {
                    _this.parseResponse(false)
                }
            });
        }
    };
}

function raisePerror(message) {
    new PNotify({
        title: 'Erro',
        text: message,
        addclass: 'bg-danger'
    });
}

function raisePsuccess(message) {
    new PNotify({
        title: 'Sucesso',
        text: message,
        addclass: 'bg-success'
    });
}

function raisePwarning(message) {
    new PNotify({
        title: 'Aviso',
        text: message,
        addclass: 'bg-warning'
    });
}