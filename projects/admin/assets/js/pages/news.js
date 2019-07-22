$(function () {

    $('#News_post_type').on('change', function () {
        if ($(this).prop('checked') === true) {
            $('input[name="width"]').val(800);
            $('input[name="height"]').val(800);
        } else {
            $('input[name="width"]').val(835);
            $('input[name="height"]').val(987);
        }
    });

    var sort_start = 0;
    var sort_end = 0;

    if(jQuery().sortable){
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
                    form.append('<input name="id_new[]" value="' + $(this).attr('data-id') + '">');
                    form.append('<input name="ordem[]" value="' + $(this).attr('data-ordem') + '">');
                });

                var data = gem_data(serialize(form));
                $.ajax({
                    url: './news/reorder/',
                    type: 'post',
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                });
            }
        });
        $('.sortable_tr').disableSelection();
    }

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

    var editor = $('.summernote');
    editor.summernote({
        height: 250,
        lang: "pt-BR",
        callbacks: {
            onPaste: function (e) {
                var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                e.preventDefault();
                document.execCommand('insertText', false, bufferText);
            }
        }
    });

    $('.remove-img').unbind('click').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var $this = $(this);
        var row = $this.closest('.row');
        var col12last = $(row.find('.col-lg-12')[1]);
        $(row.find('input[type=hidden]')[0]).val('remove');
        col12last.html('');
    });

    $('.file-input').on('change', function () {

        var $this = $(this);
        var row = $this.closest('.row');
        var col12first = $(row.find('.col-lg-12')[0]);
        var col12last = $(row.find('.col-lg-12')[1]);
        var form = $this.closest('div');
        var data = gem_data(serialize($('<form></form>').append(form.clone())));

        var width = parseInt(col12first.find('input[name=width]').val());
        var height = parseInt(col12first.find('input[name=height]').val());

        var last_url = false;

        col12first.find('button.crop').unbind('click').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var datacrop = gem_data(serialize($('<form></form>').append(col12last.find('._info').clone().append('<input type="hidden" name="file" value="' + last_url + '">'))));
            $.ajax({
                url: window.protocol + window._parent + 'upload-abstract/normal-set/',
                type: 'post',
                cache: false,
                contentType: false,
                processData: false,
                data: datacrop,
                success: function (response) {
                    response = json(response);
                    if (response) {
                        if (response.status) {
                            col12first.find('button.crop').removeClass('_block');
                            col12last.html('<img src="' + (window.protocol + response.url) + '">');
                            $(row.find('input[type=hidden]')[0]).val(response.url);
                        }
                    }
                }
            });
        });

        $.ajax({
            url: window.protocol + window._parent + 'upload-abstract/upload-normal/',
            type: 'post',
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            success: function (response) {
                response = json(response);
                if (response) {
                    if (response.status) {

                        console.log(response);

                        last_url = response.url;
                        col12first.find('button.crop').addClass('_block');
                        col12last.html('<img src="' + (window.protocol + response.url) + '"><div class="_info"><input type="hidden" name="x"><input type="hidden" name="y"><input type="hidden" name="x2"><input type="hidden" name="y2"><input type="hidden" name="w"><input type="hidden" name="h"></div>');

                        col12last.find('img').Jcrop({
                            onChange: function (c) {
                                col12last.find('input[name=x]').val(c.x);
                                col12last.find('input[name=y]').val(c.y);
                                col12last.find('input[name=x2]').val(c.x2);
                                col12last.find('input[name=y2]').val(c.y2);
                                col12last.find('input[name=w]').val(c.w);
                                col12last.find('input[name=h]').val(c.h);
                            },
                            bgColor: 'black',
                            bgOpacity: .4,
                            minSize: [width, height],
                            maxSize: [width, height],
                            setSelect: [0, 0, width, height]
                        });
                    } else {
                        col12first.find('button.crop').removeClass('_block');
                    }
                } else {
                    col12first.find('button.crop').removeClass('_block');
                }
            }
        });
    });
});