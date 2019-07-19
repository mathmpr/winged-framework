$(function () {
    var timer = 0;
    var errort = 0;
    $("#login").validate({
        errorClass: 'validation-error-label',
        successClass: 'validation-valid-label',
        rules: {
            "Winged\\Model\\Login[email]": {
                required: true,
                email: true
            },
            "Winged\\Model\\Login[senha]": {
                required: true,
                minlength: 6,
                maxlength: 16
            }
        },
        submitHandler: function (form) {
            $(form).find('button span i').hide();
            $(form).find('button span img').show();
            $('#error-login').css({display: 'none'});
            clearTimeout(errort);
            clearTimeout(timer);
            $.ajax({
                url: 'login/login',
                type: 'post',
                data: $(form).serialize(),
                success: function (response) {
                    timer = setTimeout(function () {
                        try {
                            response = $.parseJSON(response);
                        } catch (e) {
                            response = false;
                        }
                        if (response) {
                            if (response.status) {
                                window.location = 'default/'
                            } else {
                                $(form).find('button span i').show();
                                $(form).find('button span img').hide();
                                $('#error-login').text(response.message);
                                $('#error-login').css({display: 'inline'});
                                setTimeout(function () {
                                    $('#error-login').css({display: 'none'});
                                }, 3000)
                            }
                        } else {
                            $(form).find('button span i').show();
                            $(form).find('button span img').hide();
                            $('#error-login').text('Erro no servidor, aguarde e tente novamente.');
                            $('#error-login').css({display: 'inline'});
                            setTimeout(function () {
                                $('#error-login').css({display: 'none'});
                            }, 3000)
                        }
                    }, 2000);
                }
            });
        }
    });

    $("#recuperar").validate({
        errorClass: 'validation-error-label',
        successClass: 'validation-valid-label',
        rules: {
            "Winged\\Model\\Login[email]": {
                required: true,
                email: true
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    $("#redefinir").validate({
        errorClass: 'validation-error-label',
        successClass: 'validation-valid-label',
        rules: {
            "Winged\\Model\\Login[senha]": {
                required: true,
                minlength: 6,
                maxlength: 16
            },
            "Winged\\Model\\Login[repeat]": {
                required: true,
                minlength: 6,
                maxlength: 16,
                equalTo: "#Login_senha"
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });


});