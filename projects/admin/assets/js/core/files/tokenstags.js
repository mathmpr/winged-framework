var Tokens = function (selector, o_options) {
    var _this = this;
    var main = null;
    o_options = o_options || {};
    if (typeof selector === 'string') {
        selector = $(selector);
    }
    if (!selector instanceof jQuery) {
        selector = $(selector);
    }
    var options = {
        url: false,
        type: 'get',
        data: {},
        remote: false,
        names: [],
        show: false,
        cantReapeatComparison: false,
        nothingMessage: 'No results found.',
        resetOnBlur: true,
        beforeCreate: false,
        afterCreate: false,
        beforeRemove: false,
        afterRemove: false,
    };
    for (var i in o_options) {
        options[i] = o_options[i];
    }
    selector.attr('autocomplete', 'off');
    selector.closest('.form-group').addClass('tokenfield');
    var sprev = null;
    var nsel = selector.clone();
    if (selector.prev().length == 0) {
        sprev = selector.parent();
        selector.remove();
        selector = nsel;
        sprev.prepend('<div class="typehead-tokenfield form-control clearfix"></div>');
        main = sprev.find('.typehead-tokenfield');
    } else {
        sprev = selector.prev();
        selector.remove();
        selector = nsel;
        sprev.after('<div class="typehead-tokenfield form-control clearfix"></div>');
        main = sprev.next('.typehead-tokenfield');
    }
    selector.data('name', selector.attr('name')).removeAttr('name');
    selector.removeClass('form-control').css({border: 'none', height: '100%'});
    main.append(selector);
    main.on('click', function (event) {
        selector.focus();
    });

    selector.on('blur', function (event) {
        setTimeout(function () {
            main.find('.results').remove();
        }, 100);
        if (options.resetOnBlur != null && options.resetOnBlur === true) {
            selector.val('');
        }
    });
    var index;

    selector.on('keyup', function (event) {
        index = 0;
        var sl;
        var key = event.keyCode;
        if (key == 10 || key == 13) {
            sl = main.find('.selected').not('.nothing');
            if (sl.length > 0) {
                sl.trigger('click');
            }
        } else if (key == 38) {
            event.preventDefault();
            event.stopPropagation();
            sl = main.find('.selected');
            if (sl.prev().length > 0) {
                sl.removeClass('selected');
                sl.prev().addClass('selected');
            }
        } else if (key == 40) {
            event.preventDefault();
            event.stopPropagation();
            if (main.find('.selected').length == 0) {
                main.find('li:first-child').addClass('selected');
            } else {
                sl = main.find('.selected');
                if (sl.next().length > 0) {
                    sl.removeClass('selected');
                    sl.next().addClass('selected');
                }
            }
        } else if (selector.val() != '') {
            if (options.remote != null && options.remote != false) {
                if (options.url != null && options.url != false) {
                    if (options.url.indexOf('?') > -1) {
                        options.url += '&query' + selector.val();
                    }
                }
                options.data.query = selector.val();
                $.ajax({
                    url: options.url, type: options.type, data: options.data, success: function (data) {
                        try {
                            data = $.parseJSON(data);
                        } catch (e) {
                            console.log(data);
                            data = false;
                        }
                        if (data) {
                            if (typeof options.remote === 'function') {
                                options.remote(data, _this);
                            }
                        } else {
                            console.error('Parse json error. Verefy server response.');
                        }
                    }
                });
            } else if (options.ajax != null && typeof options.ajax === 'function') {
                options.ajax(selector.val(), _this);
            }
        }
    });

    selector.on('keydown', function (event) {
        var key = event.keyCode;
        if (key == 10 || key == 13 || key == 38 || key == 40) {
            event.preventDefault();
            event.stopPropagation();
        }
        if (key == 8) {
            if (selector.val() == '') {
                var l = main.find('.token');
                $(l[l.length - 1]).find('.close').trigger('click');
            }
        }
    });

    _this.clone = function (obj) {
        var nyw = {};
        if (typeof obj === 'object') {
            for (var i in obj) {
                nyw[i] = obj[i];
            }
        }
        return nyw;
    };

    _this.findWith = function (data) {
        var add;
        var finds = [];
        main.find('.token').each(function () {
            add = true;
            var self = $(this);
            for (var i in options.names) {
                var len = self.find('input[name^="' + selector.data('name') + '[' + options.names[i] + '][]"]');
                if (!(len.length > 0 && ("" + len.val() + "" == "" + data[options.names[i]] + ""))) {
                    add = false;
                }
            }
            if (add) {
                finds.push($(this));
            }
        });
        return finds;
    };

    _this.serialize = function (name) {

        name = name || false;
        var tokens;
        var nobj;
        if (name && typeof name === 'string') {
            tokens = {};
            nobj = {};
            main.find('.token').each(function () {
                var self = $(this);
                for (var t in options.names) {
                    var len = self.find('input[name^="' + selector.data('name') + '[' + options.names[t] + '][]"]');
                    if (len.length > 0) {
                        if (nobj[options.names[t]] == null) {
                            nobj[options.names[t]] = [];
                        }
                        nobj[options.names[t]].push(len.val());
                    }
                }
            });
            tokens[name] = nobj;
        } else {
            tokens = [];
            main.find('.token').each(function () {
                var self = $(this);
                nobj = {};
                for (var t in options.names) {
                    var len = self.find('input[name^="' + selector.data('name') + '[' + options.names[t] + '][]"]');
                    if (len.length > 0) {
                        nobj[options.names[t]] = len.val();
                    }
                }
                tokens.push(_this.clone(nobj));
            });
        }
        return tokens;
    };

    _this.tokenCount = function () {
        return main.find('.token').length;
    };

    _this.removeAll = function () {
        main.find('.token').remove();
    };

    _this.bindToken = function () {
        main.find('.token').unbind('click');
        main.find('.token').on('click', function (event) {
            event.stopPropagation();
        });
        main.find('.token .close').unbind('click');
        main.find('.token .close').on('click', function (event) {
            event.stopPropagation();
            var self = $(this);
            var token = self.closest('.token');
            var data = {};

            for (var t in options.names) {
                var len = token.find('input[name^="' + selector.data('name') + '[' + options.names[t] + '][]"]');
                if (len.length > 0) {
                    data[options.names[t]] = len.val();
                }
            }

            data[options.show] = token.find('.token-label').text();

            if (options.beforeRemove != false && typeof options.beforeRemove === 'function') {
                if (options.beforeRemove(data, _this) === false) {
                    return false;
                }
            }
            self.closest('.token').remove();
            if (options.afterRemove != false && typeof options.afterRemove === 'function') {
                if (options.afterRemove(data, _this) === false) {
                    return false;
                }
            }
        });
    };

    _this.loadData = function (data) {
        for (var i in data) {
            _this.createToken(data[i]);
        }
    };

    _this.createToken = function (data) {
        if (options.beforeCreate != false && typeof options.beforeCreate === 'function') {
            if (options.beforeCreate(data, _this) == false) {
                return false;
            }
        }
        console.log('aaa');
        var token = $('<div class="token"><span class="token-label">' + data[options.show] + '</span><a href="javascript:;" class="close" tabindex="-1">×</a></div>');
        for (var k in options.names) {
            if (data[options.names[k]] != null) {
                token.prepend('<input type="hidden" name="' + selector.data('name') + '[' + options.names[k] + '][]" value="' + data[options.names[k]] + '">');
            }
        }
        if (main.find('.token').length == 0) {
            main.prepend(token);
        } else {
            var l = main.find('.token');
            $(l[l.length - 1]).after(token);
        }
        _this.bindToken();
        if (options.afterCreate != false && typeof options.afterCreate === 'function') {
            if (options.afterCreate(data, _this) == false) {
                return false;
            }
        }
    };

    _this.process = function (data) {
        if (data) {
            var ul = $('<div class="results"><ul></ul></div>');
            var toCompare = {};
            if (options.cantReapeatComparison != null && options.cantReapeatComparison !== false) {
                main.find('.token').each(function () {
                    var self = $(this);
                    for (var t in options.cantReapeatComparison) {
                        var len = self.find('input[name^="' + selector.data('name') + '[' + options.cantReapeatComparison[t] + '][]"]');
                        if (len.length > 0) {
                            if (toCompare[options.cantReapeatComparison[t]] == null) {
                                toCompare[options.cantReapeatComparison[t]] = [];
                            }
                            toCompare[options.cantReapeatComparison[t]].push(len.val());
                        }
                    }
                });
            }
            var add;
            var nagate = 0;
            for (var i in data) {
                add = true;
                var li = $('<li></li>');
                for (var k in options.names) {
                    if (data[i][options.names[k]] != null) {
                        li.data(options.names[k], data[i][options.names[k]]);
                        if (options.cantReapeatComparison != null && options.cantReapeatComparison !== false) {
                            if (toCompare[options.names[k]] != null) {
                                if (toCompare[options.names[k]].indexOf("" + data[i][options.names[k]] + "") > -1) {
                                    add = false;
                                    nagate++;
                                    break;
                                }
                            }
                        }
                    }
                }
                if (data[i][options.show] != null) {
                    li.text(data[i][options.show]);
                }
                if (add) {
                    ul.find('ul').append(li);
                }
            }
            if (data.length == nagate || data.length == 0) {
                ul.find('ul').append('<li class="nothing">' + options.nothingMessage + '</li>');
            }
            selector.next('.results').remove();
            selector.after(ul);
            main.find('li').not('.nothing').on('click', function (event) {
                event.stopPropagation();
                var data = {};
                var self = $(this);
                var token = $('<div class="token"><span class="token-label">' + self.text() + '</span><a href="javascript:;" class="close" tabindex="-1">×</a></div>');
                for (var k in options.names) {
                    if (self.data(options.names[k]) != null) {
                        token.prepend('<input type="hidden" name="' + selector.data('name') + '[' + options.names[k] + '][]" value="' + self.data(options.names[k]) + '">');
                        data[options.names[k]] = self.data(options.names[k]);
                    }
                }

                if (options.beforeCreate != false && typeof options.beforeCreate === 'function') {
                    if (options.beforeCreate(data, _this) == false) {
                        return false;
                    }
                }

                if (main.find('.token').length == 0) {
                    main.prepend(token);
                } else {
                    var l = main.find('.token');
                    $(l[l.length - 1]).after(token);
                }
                _this.bindToken();
                self.closest('.results').remove();
                selector.val('');
                selector.focus();
                if (options.afterCreate != false && typeof options.afterCreate === 'function') {
                    if (options.afterCreate(data, _this) == false) {
                        return false;
                    }
                }
            });
        }
    }

    if (options.load != false) {
        _this.loadData(options.load);
    }
};