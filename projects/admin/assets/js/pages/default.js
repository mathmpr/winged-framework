var minContainerHeight = 350;
var minContainerWidth = false;

$(function () {
    $.alpaca.setDefaultLocale("pt_BR");

    $.uniform.defaults.fileButtonHtml = 'Escolha um arquivo';
    $.uniform.defaults.fileDefaultHtml = 'Nenhum arquivo selecionado';

    $(".uniform-uploader").uniform({
        fileButtonClass: 'action btn bg-blue'
    });

    $('#midia-modal').on('show.bs.modal', function () {
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
            $('#the-cropper').html('<div class="image-cropper-container"><img src="' + _self.attr('data-pure-url') + '" alt="" id="final-midia-cropper"></div>')
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

        var cropper = $('#final-midia-cropper');
        cropper.cropper({
            viewMode: 2,
            aspectRatio: 1,
            minContainerHeight: minContainerHeight,
            minContainerWidth: minWidth,
            zoomable: true,
            ready: function () {
                //cropper.cropper('rotate', 90);
            }
        });

        midiaModal.find('.aspect').unbind('change').on('change', function () {
            midiaModal.find('.aspect').each(function () {
                var _self = $(this);
                if (_self.is(':checked')) {
                    cropper.cropper('setAspectRatio', _self.val());
                }
            });
        });

        midiaModal.find('.sizes').unbind('change').on('change', function () {
            midiaModal.find('.sizes').each(function () {
                var _self = $(this);
                var sizes = _self.val().split('x');
                sizes[0] = parseInt(sizes[0]);
                sizes[1] = parseInt(sizes[1]);
                var _self = $(this);
                if (_self.is(':checked')) {
                    cropper.cropper('setAspectRatio', (sizes[0] / sizes[1]));
                }
            });
        });

        midiaModal.find('.minus').on('click', function (event) {
            event.preventDefault();
            var containerData = cropper.cropper('getContainerData');

            cropper.cropper('zoomTo', 1.8, {
                x: containerData.width / 2,
                y: containerData.height / 2
            });
        });

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

    $('#open-midia').trigger('click');

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

    function bindModalTriggers() {
        $('.open-midia-modal').unbind('click').on('click', function (event) {
            event.preventDefault();
            $('#open-midia').trigger('click');
        });
    }

    bindModalTriggers();

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
            height: 250,
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
});