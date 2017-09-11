var time = 0;
var app = {
    init: function () {
        $('#ext1').on('change', function () {
            var self = $(this);
            if (self[0].checked === true) {
                $('#db_ext').find('.none').removeClass('required').fadeOut(100, function () {
                    setTimeout(function () {
                        $('div[data-class=' + self.val() + ']').fadeIn(100).addClass('required');
                    }, 100);
                });
            }
        });
        $('#ext2').on('change', function () {
            var self = $(this);
            ;
            if (self[0].checked === true) {
                $('#db_ext').find('.none').removeClass('required').fadeOut(100, function () {
                    setTimeout(function () {
                        $('div[data-class=' + self.val() + ']').fadeIn(100).addClass('required');
                    }, 100);
                });
            }
        });
        $('input[name=dbtype]').on('change', function () {
            var self = $(this);
            var $obj = {
                DB_DRIVER_MYSQL: '<div class="none" data-type="DB_DRIVER_MYSQL"><div class="inp"><div class="enf"></div><input autocomplete="off" class="txt" name="host" type="text" placeholder="Host"/></div><div class="inp"><div class="enf"></div><input autocomplete="off" class="txt" name="user" type="text" placeholder="User"/></div><div class="inp"><div class="enf"></div><input autocomplete="off" data-ex="on" class="txt" name="password" type="text" placeholder="Password"/></div><div class="inp"><div class="enf"></div><input autocomplete="off" class="txt" name="dbname" type="text" placeholder="DB Name"/></div></div>',
                DB_DRIVER_SQLITE: '<div class="none" data-type="DB_DRIVER_SQLITE"><div class="inp"><div class="enf"></div><input autocomplete="off" class="txt" name="host" type="text"            placeholder="File Path"/></div></div>',
            };
            if (self[0].checked === true) {
                $('#db_auth').find('.none').remove();
                $('#db_auth form').prepend($($obj[self.val()]));
            }
        });
        var intv = 0;
        var inta = 0;
        $('.teste-db').on('click', function () {
            clearTimeout(intv);
            clearInterval(inta);
            var self = $(this);
            self.closest('.teste-db').find('> div').text('');
            var _data = app.serialize($(".forms"));
            var _url = URL + "winged/connection/";
            inta = setInterval(function () {
                var t = self.closest('.teste-db').find('> div').text();
                if (t == '') {
                    self.closest('.teste-db').find('> div').text('connecting.');
                } else {
                    if (t.indexOf('...') == -1) {
                        self.closest('.teste-db').find('> div').text(t + '.');
                    } else {
                        self.closest('.teste-db').find('> div').text('connecting.');
                    }
                }
            }, 400);
            intv = setTimeout(function () {
                $.ajax({
                    url: _url, type: "post", data: _data, cache: false, success: function (response) {
                        response = json(response);
                        clearInterval(inta);
                        if (response.status) {
                            self.closest('.teste-db').find('> div').html('connect success');
                            intv = setTimeout(function () {
                                self.closest('.teste-db').find('> div').html('');
                            }, 3000);
                        } else {
                            self.closest('.teste-db').find('> div').html('connect fail');
                            intv = setTimeout(function () {
                                self.closest('.teste-db').find('> div').html('');
                            }, 3000);
                        }
                    }
                });
            }, 1200);
        });

        var finish = 0;

        $('input[name=dbext]').on('change', function () {
            var self = $(this);
            if (self.val() == 1) {
                $('#cont').find('.finish').hide();
                $('#cont').find('.to-form').show();
                finish = 0;
            } else {
                $('#cont').find('.finish').show();
                $('#cont').find('.to-form').hide();
                finish = 1;
            }
        });
        $(".inp .txt").on("focus", function () {
            var self = $(this);
            self.closest(".inp").addClass("add");
        });
        $(".inp .txt").on("blur", function () {
            var self = $(this);
            self.closest(".inp").removeClass("add");
        });
        $(".inp .txt").on("keyup", function (event) {
            event.preventDefault();
            var self = $(this);
            var code = event.keyCode;
            if (code == 10 || code == 13) {
                if(finish == 1){
                    self.closest(".form").find(".finish").trigger("click");
                }else{
                    self.closest(".form").find(".enter").trigger("click");
                }
            }
        });
        $(".btns .to-form").on("click", function () {
            var self = $(this);
            var form = self.closest(".form");
            var act = self.attr("data-act");
            var to = self.attr("data-to");
            if (act == "back") {
                $(".forms .form").fadeOut(500);
                $(to).fadeIn(500);
            } else if (app.validate(form)) {
                $(".forms .form").fadeOut(500);
                $(to).fadeIn(500);
            }
        });
        $(".btns .finish").on("click", function () {
            var self = $(this);
            var form = self.closest(".form");
            if (app.validate(form)) {
                var _data = app.serialize($(".forms"));
                var _url = URL + "winged/install/";
                $.ajax({
                    url: _url, type: "post", data: _data, cache: false, success: function (response) {
                        response = json(response);
                        if (response.status) {
                            form.find(".msg").text(response.text);
                            clearTimeout(time);
                            form.find(".msg").fadeIn(500, function () {
                                time = setTimeout(function () {
                                    window.location = URL;
                                }, 3000);
                            });
                        }
                    }
                });
            }
        });
        $(".forms .form").on("submit", function (event) {
            event.preventDefault();
        });
    }, validate: function (obj) {
        var valid = true;
        var error = "";
        obj.find(".txt").each(function () {
            var input = $(this);
            if (input.val() == "" && input.attr("data-ex") == null) {
                error = "Empty inputs in form.";
                input.closest(".inp").css("border-bottom", "2px solid #FF1F1F");
                valid = false;
            } else {
                input.closest(".inp").css("border-bottom", "2px solid #00FBFF");
                if (input.attr("data-eq") != null) {
                    var name = input.attr("data-eq");
                    var eq = $("input[name=" + name + "]");
                    var value = eq.val();
                    if (value != "") {
                        if (value != input.val()) {
                            error = "Password are differents.";
                            input.closest(".inp").css("border-bottom", "2px solid #FF1F1F");
                            valid = false;
                        }
                    }
                }
            }
        });
        obj.find('.inp.required.radio').each(function () {
            var self = $(this);
            if (self.find("input[type=radio]:checked").length == 0) {
                valid = false;
                self.find('.text-in').css("color", "#FF1F1F");
                error = "Select required options.";
            } else {
                self.find('.text-in').css("color", "#00FBFF");
            }
        });
        obj.find(".msg").text(error);
        clearTimeout(time);
        obj.find(".msg").fadeIn(500, function () {
            time = setTimeout(function () {
                obj.find(".msg").fadeOut(500);
            }, 3000);
        });
        return valid;
    }, serialize: function (forms) {
        var vect = {};
        forms.find(".form").each(function () {
            var form = $(this);
            form.find(".txt, input[type=hidden], input:checked").each(function () {
                var input = $(this);
                vect[input.attr("name")] = input.val();
            });
        });
        return vect;
    }
};
$(function () {
    app.init();
});
function json(data, echo) {
    echo = echo || false;
    try {
        return $.parseJSON(data);
    } catch (e) {
        if (data != "") {
            if (echo) {
                console.log(data);
            }
        }
        return false;
    }
}