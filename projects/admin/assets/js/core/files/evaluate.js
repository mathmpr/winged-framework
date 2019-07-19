function is_bool($value) {
    if (typeof $value == "boolean") {
        return true;
    }
    return false;
}

function is_int($value) {
    return Number($value) === $value && $value % 1 === 0;
}

function is_string($value) {
    if (typeof $value == "string") {
        return true;
    }
    return false;
}

function is_null($value) {
    if ($value == null || typeof $value == "undefined" || $value == "undefined") {
        return true;
    }
    return false;
}

function is_function($value) {
    if (typeof $value == "function") {
        return true;
    }
    return false;
}

function is_object($value) {
    if (typeof $value == "object") {
        return true;
    }
    return false;
}

function is_float($value) {
    return Number($value) === $value && $value % 1 !== 0;
}

function object_name($value, $class_name) {
    if (is_object($value) && $value instanceof eval($class_name)) {
        return true;
    }
    return false;
}

function json(string, showlog) {
    showlog = (!showlog) ? false : true;
    try {
        var _json = $.parseJSON(string);
        return _json;
    } catch (e) {
        if (string != "" && showlog) {
            console.log(string);
        }
        return false;
    }
}

function is_email(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function rand(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function gem_token(token_length) {
    token_length = token_length || 4;
    if (token_length < 4) {
        token_length = 4;
    }
    var token = '';
    var words = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    for (var x = 0; x < token_length; x++) {
        var chose = rand(0, 1);
        if (chose == 0) {
            token += words[rand(0, 25)];
        } else {
            token += rand(0, 9);
        }
    }
    return token;
}

function event_keys() {
    var _self = this;
    _self.events_key_global_stack = {};
    _self.save_status = {};
    this.add_event_to = function (selector, keycode, main_call, status) {
        _self.events_key_global_stack[selector] = {};
        _self.events_key_global_stack[selector]["key"] = keycode;
        _self.events_key_global_stack[selector]["call"] = main_call;
        _self.events_key_global_stack[selector]["status"] = status;
    }
    this.remove_event_from = function (selector) {
        delete _self.events_key_global_stack[selector];
        delete _self.save_status[selector];
    }
    this.change_status_from = function (selector, status) {
        _self.events_key_global_stack[selector].status = status;
    }
    this.change_status_and_disable_all_status = function (selector, status, change_all_to_boolean) {
        for (var i in _self.save_status) {
            delete _self.save_status[i];
        }
        for (var i in _self.events_key_global_stack) {
            _self.save_status[i] = _self[i].status;
            _self[i].status = change_all_for_boolean;
        }
    }
    this.restore_status = function () {
        for (var i in _self.events_key_global_stack) {
            _self.events_key_global_stack[i] = _self[i].save_status;
        }
    }
    this.bind = function () {
        $("html").on("keyup", function (event) {
            var keyup = event.keyCode;

            for (var i in _self.events_key_global_stack) {
                if (_self.events_key_global_stack[i].key == keyup && _self.events_key_global_stack[i].status && $(i).length > 0) {
                    _self.events_key_global_stack[i].call($(i));
                }
            }
        });
    }
}

function _fade() {
    var _self = this;
    _self.objs = {};
    _self.add = function ($obj) {
        if (!is_object($obj)) {
            $obj = $($obj);
        }
        if ($obj.length > 0) {
            var fadeid = $obj.attr('fade-id');
            if (fadeid == null) {
                var token = gem_token(6);
                $obj.attr('fade-id', token);
                _self.objs[token] = {obj: $obj, timer: false};
                return true;
            }
        }
        return false;
    }
    _self.remove = function ($obj) {
        if (!is_object($obj)) {
            $obj = $($obj);
        }
        var fadeid = $obj.attr('fade-id');
        if (fadeid != null) {
            if (_self.objs[fadeid] != null) {
                delete _self.objs[fadeid];
            }
        }
    }
    _self.fade = function ($obj, time, out, call) {
        call = call || false;
        out = out || 500;
        if (!is_object($obj)) {
            $obj = $($obj);
        }
        var fadeid = $obj.attr('fade-id');
        if (fadeid != null) {
            if (_self.objs[fadeid] != null) {
                clearTimeout(_self.objs[fadeid].timer);
                _self.objs[fadeid].obj.stop().fadeIn(out);
                _self.objs[fadeid].timer = setTimeout(function () {
                    _self.objs[fadeid].obj.fadeOut(out, function () {
                        if (typeof call == 'function') {
                            call();
                        }
                    });
                }, time);
            }
        }
    }
}

function _validate(options) {

    options = options || {};
    var _self = this;
    _self.token_form = {};
    _self.lastValidation = [],

        _self.set_options = function (opt) {
            opt = opt || {};
            if (is_object(options)) {
                options = merge_properties(opt, {
                    valid: 'form_selector', //jquery form element
                    addclass: 'invalid', //string all class to add
                    element: 'html_append_prepeend', //html string
                    type: 'html', //set 'text' to add only text error in element
                    add: 'prepend', //change to append to add text or element in end of target element
                    factor: [ //navigate from current element of class .required to target element and add class seted in property 'addclass'
                        {command: 'closest', selector: '.box_input'}
                    ],
                    append_element: [ //navigate from current element of class .required to target element and add text or html element
                        {command: 'closest', selector: '.box_input'}
                    ],
                    elements: { //selector for custom configs to elements in current form

                    },
                    errors: { //standart messages erros for all css class
                        required: 'message for required field',
                        email: 'message for incorret e-mail format',
                        num: 'message for numeric value gretter or smaller',
                        length: 'message for a string length',
                        equals: 'message for a multi .required .equals same value',
                        card: 'message for invalid card number',
                        cpf: 'message for invalid cpf',
                        cnpj: 'message for invalid cnpj',
                        both: 'message for both cpf or cnpj'
                    }
                });
            }
        };

    _self.set_options(options);

    _self.last = function () {
        return _self.lastValidation;
    }

    _self.validate = function () {
        if (is_null(options.valid.attr('data-token'))) {
            var token = gem_token(6);
            options.valid.attr('data-token', token)
            _self.token_form[token] = {};
        }

        var eachn = 0;
        var _valid = true;
        _self.lastValidation = [];
        if (options) {
            options.valid.find(".required").not('*[disabled=disabled]').each(function () {
                var $self = $(this);
                var val = $self.val();
                var cl = 'required';
                var el = false;
                var execute = false;
                var selector = false;
                var individual_valid = true;
                var normal_pro = true;
                var classes = [
                    'required',
                    'email',
                    'regex',
                    'num',
                    'length',
                    'equals',
                    'card',
                    'cpf',
                    'cnpj',
                    'both',
                ];

                if (options.elements != null) {
                    for (var i in options.elements) {
                        el = options.valid.find(i);
                        if (el.is($self)) {
                            execute = el;
                            selector = i;
                        }
                    }
                }

                if (selector) {
                    if (options.elements[selector].default_behavior != null) {
                        if (options.elements[selector].default_behavior == false) {
                            normal_pro = false;
                        }
                    }
                }

                if (normal_pro) {
                    _self.remove_html($self);
                    if (val.trim() == "") {
                        _self.append($self, "required");
                        _self.paint($self);
                        _valid = false;
                        individual_valid = false;
                    } else {
                        _self.remove($self);
                        _self.nopaint($self);
                    }
                    _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                    eachn++;

                    if ($self.hasClass("email")) {
                        cl = 'email';
                        if (!is_email(val)) {
                            _self.append($self, "email");
                            _self.paint($self);
                            _valid = false;
                            individual_valid = false;
                        } else {
                            _self.remove($self);
                            _self.nopaint($self);
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass("regex")) {
                        cl = 'regex';

                        if (options.elements != null || msg == '') {
                            for (var i in options.elements) {
                                el = options.valid.find(i);
                                if (el.is($self) && el.hasClass('regex')) {
                                    if (options.elements[i].regex != null) {
                                        el = options.elements[i].regex;
                                    }
                                    break;
                                }
                            }
                        }

                        if (!el) {
                            if ($self.attr('data-regex') != null) {
                                el = $self.attr('data-regex');
                            }
                        }

                        if (el) {
                            var match = el.match(new RegExp('^/(.*?)/([gimy]*)$'));
                            var regex = new RegExp(match[1], match[2]);
                            if (!regex.test(val)) {
                                _self.append($self, "regex");
                                _self.paint($self);
                                _valid = false;
                                individual_valid = false;
                            } else {
                                _self.remove($self);
                                _self.nopaint($self);
                            }
                        } else {
                            _self.append($self, "regex");
                            _self.paint($self);
                            _valid = false;
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass("num")) {
                        cl = 'num';
                        var imax = parseInt($self.attr("data-max"));
                        var imin = parseInt($self.attr("data-min"));

                        val = parseInt(val);

                        if (is_int(imax) && is_int(imin)) {
                            if (!(val > imin && val < imax)) {
                                _self.append($self, "num");
                                _self.paint($self);
                                _valid = false;
                                individual_valid = false;
                            } else {
                                _self.remove($self);
                                _self.nopaint($self);
                            }
                        }
                        else if (is_int(imax)) {
                            if (!(val < imax)) {
                                _self.append($self, "num");
                                _self.paint($self);
                                _valid = false;
                                individual_valid = false;
                            } else {
                                _self.remove($self);
                                _self.nopaint($self);
                            }
                        }
                        else if (is_int(imin)) {
                            if (!(val > imax)) {
                                _self.append($self, "num");
                                _self.paint($self);
                                _valid = false;
                                individual_valid = false;
                            } else {
                                _self.remove($self);
                                _self.nopaint($self);
                            }
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass("length")) {
                        cl = 'length';
                        var imax = parseInt($self.attr("data-max"));
                        var imin = parseInt($self.attr("data-min"));

                        if (imax && imin) {
                            if (val.length < imin || val.length > imax) {
                                _self.append($self, "length");
                                _self.paint($self);
                                _valid = false;
                                individual_valid = false;
                            } else {
                                _self.remove($self);
                                _self.nopaint($self);
                            }
                        }
                        else if (imax) {
                            if (val > imax) {
                                _self.append($self, "length");
                                _self.paint($self);
                                _valid = false;
                                individual_valid = false;
                            } else {
                                _self.remove($self);
                                _self.nopaint($self);
                            }
                        }
                        else if (imin) {
                            if (val < imin) {
                                _self.append($self, "length");
                                _self.paint($self);
                                _valid = false;
                                individual_valid = false;
                            } else {
                                _self.remove($self);
                                _self.nopaint($self);
                            }
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass('equals')) {
                        cl = 'equals';
                        var target = $self.attr('equal-target');
                        var obj = $(target);
                        var invalid = true;
                        if (obj.length > 0) {
                            var ob_val = obj.val();
                            if (ob_val != null) {
                                if (ob_val == val && ob_val != '' && val != '') {
                                    _self.remove($self);
                                    _self.nopaint($self);
                                    _self.remove(obj);
                                    _self.nopaint(obj);
                                    invalid = false;
                                }
                            }
                        }
                        if (invalid) {
                            _self.append($self, "equals");
                            _self.paint($self);
                            _self.append(obj, "equals");
                            _self.paint(obj);
                            _valid = false;
                            individual_valid = false;
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass('card')) {
                        cl = 'card';
                        if (_self.isCard(val)) {
                            _self.remove($self);
                            _self.nopaint($self);
                        } else {
                            _self.append($self, 'card');
                            _self.paint($self);
                            _valid = false;
                            individual_valid = false;
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass('cpf')) {
                        cl = 'cpf';
                        if (_self.isCpf(val)) {
                            _self.remove($self);
                            _self.nopaint($self);
                        } else {
                            _self.append($self, "cpf");
                            _self.paint($self);
                            _valid = false;
                            individual_valid = false;
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass('cnpj')) {
                        cl = 'cnpj';
                        if (_self.isCnpj(val)) {
                            _self.remove($self);
                            _self.nopaint($self);
                        } else {
                            _self.append($self, "cnpj");
                            _self.paint($self);
                            _valid = false;
                            individual_valid = false;
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }

                    if ($self.hasClass('both')) {
                        cl = 'both';
                        if (_self.isCnpj(val) || _self.isCpf(val)) {
                            _self.remove($self);
                            _self.nopaint($self);
                        } else {
                            _self.append($self, "both");
                            _self.paint($self);
                            _valid = false;
                            individual_valid = false;
                        }
                        _self.pushDebug(eachn, $self, _valid, cl, individual_valid);
                        eachn++;
                    }
                }

                if (selector) {
                    var iterator = options.elements[selector];
                    for (var i in iterator) {
                        if (in_array(i, classes)) {
                            if (is_function(iterator[i])) {
                                var toval = '';
                                if (execute[0].type == 'file') {
                                    if (execute[0].files.length == 0) {
                                        toval = false;
                                    } else {
                                        if (execute[0].multiple == true) {
                                            toval = execute[0].files;
                                        } else {
                                            toval = execute[0].files[0];
                                        }
                                    }
                                } else {
                                    toval = execute.val();
                                }
                                var ret = iterator[i](toval, execute, _self);
                                if (ret === false) {
                                    _valid = false;
                                }
                            }
                        }
                    }
                }

            });
        }
        return _valid;
    };

    _self.pushDebug = function (count, obj, valid, cl, ind) {
        _self.lastValidation[count] = {
            'element': obj,
            'valid': valid,
            'class_found': cl,
            'individual_valid': ind,
        }
    };

    _self.isCpf = function (cpf) {
        cpf = cpf.replace(/[^\d]+/g, '');
        if (cpf == '') return false;
        if (cpf.length != 11 ||
            cpf == "00000000000" ||
            cpf == "11111111111" ||
            cpf == "22222222222" ||
            cpf == "33333333333" ||
            cpf == "44444444444" ||
            cpf == "55555555555" ||
            cpf == "66666666666" ||
            cpf == "77777777777" ||
            cpf == "88888888888" ||
            cpf == "99999999999")
            return false;

        var add = 0;
        for (var i = 0; i < 9; i++)
            add += parseInt(cpf.charAt(i)) * (10 - i);
        var rev = 11 - (add % 11);
        if (rev == 10 || rev == 11)
            rev = 0;
        if (rev != parseInt(cpf.charAt(9)))
            return false;

        add = 0;
        for (i = 0; i < 10; i++)
            add += parseInt(cpf.charAt(i)) * (11 - i);
        rev = 11 - (add % 11);
        if (rev == 10 || rev == 11)
            rev = 0;
        if (rev != parseInt(cpf.charAt(10)))
            return false;
        return true;
    };

    _self.isCnpj = function (cnpj) {

        cnpj = cnpj.replace(/[^\d]+/g, '');

        if (cnpj == '') return false;

        if (cnpj.length != 14)
            return false;

        if (cnpj == "00000000000000" ||
            cnpj == "11111111111111" ||
            cnpj == "22222222222222" ||
            cnpj == "33333333333333" ||
            cnpj == "44444444444444" ||
            cnpj == "55555555555555" ||
            cnpj == "66666666666666" ||
            cnpj == "77777777777777" ||
            cnpj == "88888888888888" ||
            cnpj == "99999999999999")
            return false;

        var size = cnpj.length - 2
        var numbers = cnpj.substring(0, size);
        var digits = cnpj.substring(size);
        var sum = 0;
        var pos = size - 7;
        for (var i = size; i >= 1; i--) {
            sum += numbers.charAt(size - i) * pos--;
            if (pos < 2)
                pos = 9;
        }
        var result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result != digits.charAt(0))
            return false;

        size = size + 1;
        numbers = cnpj.substring(0, size);
        sum = 0;
        pos = size - 7;
        for (i = size; i >= 1; i--) {
            sum += numbers.charAt(size - i) * pos--;
            if (pos < 2)
                pos = 9;
        }
        result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result != digits.charAt(1))
            return false;

        return true;

    };

    _self.isCard = function (number) {
        number = number.replace(/[^\d]+/g, '');
        var cards = {
            Visa: /^4[0-9]{12}(?:[0-9]{3})/,
            Mastercard: /^5[1-5][0-9]{14}/,
            Amex: /^3[47][0-9]{13}/,
            DinersClub: /^3(?:0[0-5]|[68][0-9])[0-9]{11}/,
            Discover: /^6(?:011|5[0-9]{2})[0-9]{12}/,
            JCB: /^(?:2131|1800|35\d{3})\d{11}/
        };
        for (var i in cards) if (number.match(cards[i])) return i;
        return false;
    };

    _self.get_token = function () {
        return options.valid.attr('data-token');
    };

    _self.paint = function ($obj) {
        $obj = _self.make_obj($obj);
        $obj.addClass(options.addclass);
    };

    _self.nopaint = function ($obj) {
        $obj = _self.make_obj($obj);
        $obj.removeClass(options.addclass);
    };


    _self.remove_html = function ($obj) {
        $obj = _self.make_append_obj($obj);
        if (!is_null(_self.token_form[_self.get_token()].error_element)) {
            $obj.find(_self.token_form[_self.get_token()].error_element).remove();
        }
    };

    _self.append = function ($obj, _class) {
        var $copy = $obj;
        $obj = _self.make_append_obj($obj);
        var msg = '';
        if (options.errors[_class] != null) {
            msg = options.errors[_class];
        }

        if (options.elements != null || msg == '') {
            for (var i in options.elements) {
                var el = options.valid.find(i);
                if (el.is($copy)) {
                    if (options.elements[i].messages != null) {
                        if (options.elements[i].messages[_class] != null) {
                            msg = options.elements[i].messages[_class];
                        }
                    }
                    break;
                }
            }
        }

        if ($copy.attr("datafor-" + _class + "") != null || msg == '') {
            msg = $copy.attr("datafor-" + _class + "");
        }

        if (msg == '') {
            msg = 'no message was defined';
        }

        if (options.type == 'html') {
            var last = null;
            var first = null;
            if (msg != null) {
                if (options.add == 'prepend') {
                    if (is_null(_self.token_form[_self.get_token()].error_element)) {
                        $obj.prepend(options.element.replace('__replace__', msg));
                    } else {
                        if ($obj.find(_self.token_form[_self.get_token()].error_element).length == 0) {
                            $obj.prepend(options.element.replace('__replace__', msg));
                        }
                    }
                } else {
                    if (is_null(_self.token_form[_self.get_token()].error_element)) {
                        $obj.append(options.element.replace('__replace__', msg));
                    } else {
                        if ($obj.find(_self.token_form[_self.get_token()].error_element).length == 0) {
                            $obj.append(options.element.replace('__replace__', msg));
                        }
                    }
                }
                if (is_null(_self.token_form[_self.get_token()].error_element)) {
                    first = $obj[0].firstElementChild;
                    last = $obj[0].lastElementChild;
                    var idstr = '';
                    if (options.add == 'prepend') {
                        if (first.id != '') {
                            idstr = '#' + first.id;
                        }
                        _self.token_form[_self.get_token()].error_element = first.nodeName.toLowerCase() + idstr + '.' + first.className.split(' ').join('.');
                    } else {
                        if (last.id != '') {
                            idstr = '#' + last.id;
                        }
                        _self.token_form[_self.get_token()].error_element = last.nodeName.toLowerCase() + idstr + '.' + last.className.split(' ').join('.');
                    }
                }
            }

        } else {
            if (msg != null) {
                if ($obj.find(options.element).length == 0) {
                    if (options.add == 'prepend') {
                        $obj.prepend("<" + options.element + ">" + msg + "</" + options.element + ">");
                    } else {
                        $obj.append("<" + options.element + ">" + msg + "</" + options.element + ">");
                    }
                }
            }
        }
    };

    _self.remove = function ($obj) {
        var $copy = $obj;
        $obj = _self.make_append_obj($obj);
        if (options.type == 'html') {
            _self.remove_html($copy);
        } else {
            $obj.find(options.element).remove();
        }

    };

    _self.make_obj = function ($obj) {
        for (var i in options.factor) {
            var now = options.factor[i];
            if (now.command == "closest") {
                $obj = $obj.closest(now.selector);
            } else if (now.command == "parent") {
                $obj = $obj.parent(now.selector);
            } else if (now.command == "find") {
                $obj = $obj.find(now.selector);
            }
        }
        return $obj;
    };

    _self.make_append_obj = function ($obj) {
        for (var i in options.append_element) {
            var now = options.append_element[i];
            if (now.command == "closest") {
                $obj = $obj.closest(now.selector);
            } else if (now.command == "parent") {
                $obj = $obj.parent(now.selector);
            } else if (now.command == "find") {
                $obj = $obj.find(now.selector);
            }
        }
        return $obj;
    }

}


function loadbar(options) {
    options = options || {};
    var _self = this;
    _self.stopa = (!is_null(options["stopin"])) ? options["stopin"] : 80;
    _self.css_class = (!is_null(options["css-class"])) ? options["css-class"] : 150;
    _self.percent = 0;
    _self.mload = $("._loadbar_");
    _self.bload = $("._loadbar_ .loader");
    _self.timer = false;
    _self.cont = false;
    _self.animate = function (callback) {
        callback = callback || false;
        _self.timer = setInterval(function () {
            if (_self.percent < 95) {
                _self.percent += 5;
            }
            _self.bload.css("width", "" + _self.percent + "%");
            var cssw = _self.bload.width();
            var cssa = _self.mload.width();
            var testp = cssw * 100 / cssa;
            if (testp >= _self.stopa) {
                clearInterval(_self.timer);
                if (is_function(callback)) {
                    callback();
                }
            }
        }, 200);
    }

    _self.set_css = function (css_class) {
        var classes = ["white", "blue", "yellow", "red", "green"];
        for (var i in classes) {
            if (_self.bload.hasClass(classes[i])) {
                _self.bload.removeClass(classes[i]);
            }
        }
        _self.bload.addClass(css_class);
    }

    _self.set_css(_self.css_class);

    _self.continue = function (callback) {
        callback = callback || false;
        _self.cont = setInterval(function () {
            var cssw = _self.bload.width();
            var cssa = _self.mload.width();
            var testp = cssw * 100 / cssa;
            if (testp >= _self.stopa) {
                _self.percent = 100;
                _self.bload.css("width", "" + _self.percent + "%");
                clearInterval(_self.cont);
                _self.cont = setInterval(function () {
                    var cssw = _self.bload.width();
                    var cssa = _self.mload.width();
                    var testp = cssw * 100 / cssa;
                    if (testp > 99) {
                        clearInterval(_self.cont);
                        setTimeout(function () {
                            _self.stop();
                        }, 500)
                    }
                }, 10)
                if (is_function(callback)) {
                    callback();
                }
            }
        }, 10);

    }

    _self.stop = function (callback) {
        callback = callback || false;
        clearInterval(_self.cont);
        _self.bload.css("width", "0%");
        _self.percent = 0;
        if (is_function(callback)) {
            callback();
        }
    }

    _self.reanimate = function (callback) {
        callback = callback || false;
        _self.stop();
        _self.animate(callback);
    }
}

function windows(empty, keyup_obj) {
    keyup_obj = keyup_obj || false;
    empty = empty || false;
    var _self = this;
    _self.window_bg = $(".window-bg");
    _self.window = $(".windows");
    _self.bind = function (callback) {
        callback = callback || false;
        _self.window.find(".close-btn").on("click", function (event) {
            var $self = $(this);
            $("main").fadeIn(500);
            _self.window.fadeOut(500);
            _self.window_bg.fadeOut(500, function () {
                if (empty) {
                    _self.window.empty();
                }
            });
            if (keyup_obj) {
                keyup_obj.remove_event_from(".windows");
            }
        });
        _self.window_bg.on("click", function (event) {
            _self.window.trigger("click");
        });

        if (keyup_obj) {
            keyup_obj.add_event_to(".windows", 27, function () {
                _self.window.find(".close-btn").trigger("click");
            }, true);
        }

        if (is_function(callback)) {
            callback();
        }
    }
    _self.appear = function (callback) {
        callback = callback || false;
        $("main").fadeOut(500);
        _self.window.fadeIn(500);
        _self.window_bg.fadeIn(500);
        $("body, html").animate({
            scrollTop: _self.window.offset().top,
        }, 500);
        if (is_function(callback)) {
            callback();
        }
    }
    _self.unbind = function (callback) {
        callback = callback || false;
        _self.window.find("close-btn").unbind("click");
        _self.window_bg.unbind("click");
        if (is_function(callback)) {
            callback();
        }
    }
    _self.html = function (html) {
        _self.window.html(html);
    }
}

String.prototype.replaceAll = String.prototype.replaceAll || function (needle, replacement) {
        return this.split(needle).join(replacement);
    };

function in_array(needle, haystack) {
    for (var i in haystack) {
        if (haystack[i] === needle) {
            return i;
        }
    }
    return false;
}

function array_key_exists(key, haystack) {
    for (var i in haystack) {
        if (i === key) {
            return true;
        }
    }
    return false;
}

function checked(obj) {
    obj = obj || false;
    if (object_name(obj, 'jQuery')) {
        return obj.attr('checked') != null ? true : false;
    }
    return false;
}

function gem_data(__serialize) {
    var data = new FormData();
    for (var i in __serialize) {
        if (__serialize[i] instanceof Array) {
            for (var k in __serialize[i]) {
                data.append(i + '[]', __serialize[i][k]);
            }
        } else {
            data.append(i, __serialize[i]);
        }
    }
    return data;
}

function serialize(form) {
    var objs = form.serializeArray();
    var obj = {};
    for (var i in objs) {
        var name = objs[i].name.replace('[]', '');
        var value = objs[i].value;
        if (obj[name] != null) {
            if (obj[name] instanceof Array) {
                obj[name].push(value);
            } else {
                obj[name] = [obj[name]];
                obj[name].push(value);
            }
        } else {
            obj[name] = value;
        }
    }

    form.find('input[type=file]').each(function () {
        var name = this.name.replace('[]', '');
        var files = this.files;
        var value = false;
        if (files.length > 0) {
            for (var i in files) {
                if (files[i] instanceof File) {
                    value = files[i];
                    if (obj[name] != null) {
                        if (obj[name] instanceof Array) {
                            obj[name].push(value);
                        } else {
                            obj[name] = [obj[name]];
                            obj[name].push(value);
                        }
                    } else {
                        obj[name] = value;
                    }
                }
            }
        }
    });

    return obj;
}

function merge_properties(obj_o, obj_t) {

    obj_o = obj_o || false;
    obj_t = obj_t || false;

    if (!obj_o || !obj_t) {
        return false;
    }

    var obj_th = {};
    for (var i in obj_o) {
        obj_th[i] = obj_o[i];
    }
    for (var v in obj_t) {
        if (obj_th[v] == null) {
            obj_th[v] = obj_t[v];
        }
    }
    return obj_th;
}

function url2array(url) {
    var request = {};
    var pairs = url.substring(url.indexOf('?') + 1).split('&');
    for (var i = 0; i < pairs.length; i++) {
        if (!pairs[i])
            continue;
        var pair = pairs[i].split('=');
        request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
    }

    var ob = {};

    for (var i in request) {
        var index_s = i.indexOf('[');
        var index_e = i.indexOf(']');
        if (index_e != -1 && index_s != -1) {
            var exp = i.split('[');
            if (ob[exp[0].trim()] == null) {
                ob[exp[0].trim()] = [];
            }
            ob[exp[0].trim()].push(request[i]);
        } else {
            ob[i] = request[i];
        }
    }

    return ob;

}

function array2url(array) {
    var pairs = [];
    for (var key in array)
        if (array.hasOwnProperty(key))

            pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(array[key]));
    return pairs.join('&');
}

function clone(obj) {
    var n = {};
    if (is_object(obj)) {
        for (var i in obj) {
            n[i] = obj[i];
        }
    }
    return n;
}

(function($) {
    "use strict";
    $.fn.openSelect = function()
    {
        return this.each(function(idx,domEl) {
            if (document.createEvent) {
                var event = document.createEvent("MouseEvents");
                event.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                domEl.dispatchEvent(event);
            } else if (element.fireEvent) {
                domEl.fireEvent("onmousedown");
            }
        });
    }
}(jQuery));