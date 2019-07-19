$(function () {
    $(".dataTables_length select").select2({
        minimumResultsForSearch: 1 / 0,
        width: "auto"
    }), $("form[name=form_list] select[name=limit]").change(function (a) {
        var form = $(this).closest('form');
        var url = form.attr('action');
        var exp = url.split('?')[0] + '?';
        var arr = url2array(url);
        var f = true;
        for (var i in arr) {
            if (arr[i] != '' && i != 'limit') {
                if (f) {
                    f = false;
                    exp += i + '=' + arr[i];
                } else {
                    exp += '&' + i + '=' + arr[i];
                }
            }
        }
        if (exp[exp.length - 1] == '?') {
            exp += 'limit=' + $("select[name=limit]").val();
        } else {
            exp += '&limit=' + $("select[name=limit]").val();
        }
        window.location = exp;
    });

    $('form[name=form_list]').on('submit', function (event) {
        event.preventDefault();
        var $this = $('form[name=form_list] input[name=search]');
        var form = $this.closest('form');
        var url = form.attr('action');
        var exp = url.split('?')[0] + '?';
        var arr = url2array(url);
        var f = true;
        for (var i in arr) {
            if (arr[i] != '' && i != 'search') {
                if (f) {
                    f = false;
                    exp += i + '=' + arr[i];
                } else {
                    exp += '&' + i + '=' + arr[i];
                }
            }
        }
        if (exp[exp.length - 1] == '?') {
            exp += 'search=' + $("input[name=search]").val();
        } else {
            exp += '&search=' + $("input[name=search]").val();
        }
        window.location = exp;
    });
});

