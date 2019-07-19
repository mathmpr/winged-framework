var Tokens = function (selector, o_options) {
    var _this = this;
    _this.main = null;
    o_options = o_options || {};
    if (typeof selector === 'string') {
        selector = $(selector);
    }
    if (!selector instanceof jQuery) {
        selector = $(selector);
    }
    _this.options = {
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
        noClosureOnLoad: true,
    };
    for (var i in o_options) {
        _this.options[i] = o_options[i];
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
        _this.main = sprev.find('.typehead-tokenfield');
    } else {
        sprev = selector.prev();
        selector.remove();
        selector = nsel;
        sprev.after('<div class="typehead-tokenfield form-control clearfix"></div>');
        _this.main = sprev.next('.typehead-tokenfield');
    }
    selector.data('name', selector.attr('name')).removeAttr('name');
    selector.removeClass('form-control').css({border: 'none', height: '33px', padding: '0 10px'});
    _this.main.append(selector);
    _this.main.on('click', function (event) {
        selector.focus();
    });

    selector.on('blur', function (event) {
        setTimeout(function () {
            _this.main.find('.results').remove();
        }, 200);
        if (_this.options.resetOnBlur != null && _this.options.resetOnBlur === true) {
            selector.val('');
        }
    });

    var index;

    selector.on('keyup', function (event) {
        index = 0;
        var sl;
        var key = event.keyCode;
        if (key == 10 || key == 13) {
            sl = _this.main.find('.selected').not('.nothing');
            if (sl.length > 0) {
                sl.trigger('click');
            }
        } else if (key == 38) {
            event.preventDefault();
            event.stopPropagation();
            sl = _this.main.find('.selected');
            if (sl.prev().length > 0) {
                sl.removeClass('selected');
                sl.prev().addClass('selected');
            }
        } else if (key == 40) {
            event.preventDefault();
            event.stopPropagation();
            if (_this.main.find('.selected').length == 0) {
                _this.main.find('li:first-child').addClass('selected');
            } else {
                sl = _this.main.find('.selected');
                if (sl.next().length > 0) {
                    sl.removeClass('selected');
                    sl.next().addClass('selected');
                }
            }
        } else if (selector.val() != '') {
            if (_this.options.remote != null && _this.options.remote != false) {
                if (_this.options.url != null && _this.options.url != false) {
                    if (_this.options.url.indexOf('?') > -1) {
                        _this.options.url += '&query' + selector.val();
                    }
                }
                _this.options.data.query = selector.val();
                $.ajax({
                    url: _this.options.url,
                    type: _this.options.type,
                    data: _this.options.data,
                    success: function (data) {
                        try {
                            data = $.parseJSON(data);
                        } catch (e) {
                            console.log(data);
                            data = false;
                        }
                        if (data) {
                            if (typeof _this.options.remote === 'function') {
                                _this.options.remote(data, _this);
                            }
                        } else {
                            console.error('Parse json error. Verefy server response.');
                        }
                    }
                });
            } else if (_this.options.ajax != null && typeof _this.options.ajax === 'function') {
                _this.options.ajax(selector.val(), _this);
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
                var l = _this.main.find('.token');
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
        _this.main.find('.token').each(function () {
            add = true;
            var self = $(this);
            for (var i in _this.options.names) {
                var len = self.find('input[name^="' + selector.data('name') + '[' + _this.options.names[i] + '][]"]');
                if (!(len.length > 0 && ("" + len.val() + "" == "" + data[_this.options.names[i]] + ""))) {
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
            _this.main.find('.token').each(function () {
                var self = $(this);
                for (var t in _this.options.names) {
                    var len = self.find('input[name^="' + selector.data('name') + '[' + _this.options.names[t] + '][]"]');
                    if (len.length > 0) {
                        if (nobj[_this.options.names[t]] == null) {
                            nobj[_this.options.names[t]] = [];
                        }
                        nobj[_this.options.names[t]].push(len.val());
                    }
                }
            });
            tokens[name] = nobj;
        } else {
            tokens = [];
            _this.main.find('.token').each(function () {
                var self = $(this);
                nobj = {};
                for (var t in _this.options.names) {
                    var len = self.find('input[name^="' + selector.data('name') + '[' + _this.options.names[t] + '][]"]');
                    if (len.length > 0) {
                        nobj[_this.options.names[t]] = len.val();
                    }
                }
                tokens.push(_this.clone(nobj));
            });
        }
        return tokens;
    };

    _this.tokenCount = function () {
        return _this.main.find('.token').length;
    };

    _this.removeAll = function () {
        _this.main.find('.token').remove();
    };

    _this.bindToken = function () {
        _this.main.find('.token').unbind('click');
        _this.main.find('.token').on('click', function (event) {
            event.stopPropagation();
        });
        _this.main.find('.token .close').unbind('click');
        _this.main.find('.token .close').on('click', function (event) {
            event.stopPropagation();
            var self = $(this);
            var token = self.closest('.token');
            var data = {};

            for (var t in _this.options.names) {
                var len = token.find('input[name^="' + selector.data('name') + '[' + _this.options.names[t] + '][]"]');
                if (len.length > 0) {
                    data[_this.options.names[t]] = len.val();
                }
            }

            data[_this.options.show] = token.find('.token-label').text();

            if (_this.options.beforeRemove != false && typeof _this.options.beforeRemove === 'function') {
                if (_this.options.beforeRemove(data, _this) === false) {
                    return false;
                }
            }
            self.closest('.token').remove();
            if (_this.options.afterRemove != false && typeof _this.options.afterRemove === 'function') {
                if (_this.options.afterRemove(data, _this) === false) {
                    return false;
                }
            }
        });
    };

    _this.loadData = function (data) {
        for (var i in data) {
            if (_this.options.noClosureOnLoad) {
                _this.createToken(data[i], true);
            } else {
                _this.createToken(data[i]);
            }
        }
    };

    _this.createToken = function (data, beggin) {
        beggin = beggin || false;
        if (_this.options.beforeCreate != false && typeof _this.options.beforeCreate === 'function') {
            if (_this.options.beforeCreate(data, _this) == false) {
                return false;
            }
        }
        var token = $('<div class="token"><span class="token-label">' + data[_this.options.show] + '</span><a href="javascript:;" class="close" tabindex="-1">×</a></div>');
        for (var k in _this.options.names) {
            if (data[_this.options.names[k]] != null) {
                token.prepend('<input type="hidden" name="' + selector.data('name') + '[' + _this.options.names[k] + '][]" value="' + data[_this.options.names[k]] + '">');
            }
        }
        if (_this.main.find('.token').length == 0) {
            _this.main.prepend(token);
        } else {
            var l = _this.main.find('.token');
            $(l[l.length - 1]).after(token);
        }
        _this.bindToken();

        if (_this.options.afterCreate != false && typeof _this.options.afterCreate === 'function' && !beggin) {
            if (_this.options.afterCreate(data, _this) == false) {
                return false;
            }
        }
    };

    _this.process = function (data) {
        if (data) {
            var ul = $('<div class="results"><ul></ul></div>');
            var toCompare = {};
            if (_this.options.cantReapeatComparison != null && _this.options.cantReapeatComparison !== false) {
                _this.main.find('.token').each(function () {
                    var self = $(this);
                    for (var t in _this.options.cantReapeatComparison) {
                        var len = self.find('input[name^="' + selector.data('name') + '[' + _this.options.cantReapeatComparison[t] + '][]"]');
                        if (len.length > 0) {
                            if (toCompare[_this.options.cantReapeatComparison[t]] == null) {
                                toCompare[_this.options.cantReapeatComparison[t]] = [];
                            }
                            toCompare[_this.options.cantReapeatComparison[t]].push(len.val());
                        }
                    }
                });
            }
            var add;
            var nagate = 0;
            for (var i in data) {
                add = true;
                var li = $('<li></li>');
                for (var k in _this.options.names) {
                    if (data[i][_this.options.names[k]] != null) {
                        li.data(_this.options.names[k], data[i][_this.options.names[k]]);
                        if (_this.options.cantReapeatComparison != null && _this.options.cantReapeatComparison !== false) {
                            if (toCompare[_this.options.names[k]] != null) {
                                if (toCompare[_this.options.names[k]].indexOf("" + data[i][_this.options.names[k]] + "") > -1) {
                                    add = false;
                                    nagate++;
                                    break;
                                }
                            }
                        }
                    }
                }
                if (data[i][_this.options.show] != null) {
                    li.text(data[i][_this.options.show]);
                }
                if (add) {
                    ul.find('ul').append(li);
                }
            }
            if (data.length == nagate || data.length == 0) {
                ul.find('ul').append('<li class="nothing">' + _this.options.nothingMessage + '</li>');
            }
            selector.next('.results').remove();
            selector.after(ul);
            _this.main.find('li').not('.nothing').on('click', function (event) {
                event.stopPropagation();
                var data = {};
                var self = $(this);
                var token = $('<div class="token"><span class="token-label">' + self.text() + '</span><a href="javascript:;" class="close" tabindex="-1">×</a></div>');
                for (var k in _this.options.names) {
                    if (self.data(_this.options.names[k]) != null) {
                        token.prepend('<input type="hidden" name="' + selector.data('name') + '[' + _this.options.names[k] + '][]" value="' + self.data(_this.options.names[k]) + '">');
                        data[_this.options.names[k]] = self.data(_this.options.names[k]);
                    }
                }

                if (_this.options.beforeCreate != false && typeof _this.options.beforeCreate === 'function') {
                    if (_this.options.beforeCreate(data, _this) == false) {
                        return false;
                    }
                }

                if (_this.main.find('.token').length == 0) {
                    _this.main.prepend(token);
                } else {
                    var l = _this.main.find('.token');
                    $(l[l.length - 1]).after(token);
                }
                _this.bindToken();
                self.closest('.results').remove();
                selector.val('');
                selector.focus();
                if (_this.options.afterCreate != false && typeof _this.options.afterCreate === 'function') {
                    if (_this.options.afterCreate(data, _this) == false) {
                        return false;
                    }
                }
            });
        }
    }

    if (_this.options.load != false) {
        _this.loadData(_this.options.load);
    }
};