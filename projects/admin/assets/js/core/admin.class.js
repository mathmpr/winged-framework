function redirectTo(to, register, _this, _blank) {
    to = typeof to !== 'undefined' ? to : false;
    register = typeof register !== 'undefined' ? register : false;
    _this = typeof _this !== 'undefined' ? _this : false;
    _blank = typeof _blank !== 'undefined' ? _blank : false;
    if (to && register) {
        Cookies.set('from_url', register, {expires: 1});
        if (to.indexOf('http://') != -1 || to.indexOf('https://') != -1) {
            if (_blank) {
                window.open(to, '_blank')
            } else {
                window.location = to;
            }
        } else {
            if (_blank) {
                window.open(window.protocol + to, '_blank');
            } else {
                window.location = window.protocol + to;
            }
        }
    }
    return _this;
}
function confirmDelete(to, register, _this) {
    to = typeof to !== 'undefined' ? to : false;
    register = typeof register !== 'undefined' ? register : false;
    _this = typeof _this !== 'undefined' ? _this : false;
    if (to && register) {
        Cookies.set('from_url', register, {expires: 1});
        $('#comfirm-delete').trigger('click');
        $('#render-delete').attr('onclick', 'window.location="' + window.protocol + to + '"');
    }
    return _this;
}