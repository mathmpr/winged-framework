function _Form(form, options){
    options = options || false; 
    var _self = this;
    _self.ofunc = false;
    _self.timeout = false;
    _self.form = null;
    _self.default_error_border_color = "#ff4545";
    _self.default_normal_border_color = false;
    _self.default_border_width = "1px";
    _self.default_label_error_color = "#ff4545";
    _self.default_label_normal_color = false;
    _self.after_submit_insert = false;
    _self.before_submit_insert = false;
    _self.default_messages = {
        "empty" : "Empty fields on form.",
        "select" : "Select fields not selected.",
        "radio_check" : "Radios on form not checked.",
        "check_check" : "Checkboxs on form not checked.",
        "warn" : "Uma regra especifica no formulario não foi seguida."
    };
    _self.out_valid_rules = {

    };
    if(typeof form == "object"){
        this.form = form;
    }else{
        var obj = $(form);
        if(obj.length > 0){
            _self.form = $(form);
            if(options["default_error_border_color"] != null){
                _self.default_error_border_color = options["default_error_border_color"];
            }
            if(options["default_normal_border_color"] != null){
                _self.default_normal_border_color = options["default_normal_border_color"];
            }else{
                _self.default_normal_border_color = _self.form.find(".ant").css("border-color");
            }
            if(options["default_label_error_color"] != null){
                _self.default_label_error_color = options["default_label_error_color"];
            }
            if(options["default_label_normal_color"] != null){
                _self.default_label_normal_color = options["default_label_normal_color"];
            }else{
                if(_self.form.find(".ant").find(".label").css("color").length > 0){
                    _self.default_label_normal_color = _self.form.find(".ant").find(".label").css("color");
                }else{
                    _self.default_label_normal_color = "#444";
                }               
            }
            if(options["default_border_width"] != null){
                _self.default_border_width = options["default_border_width"];
            }
            if(options["messages"] != null){
                for(i in options["messages"]){
                    if(_self.default_messages[i] != null){
                        _self.default_messages[i] = options["messages"][i];
                    }
                }
            }
            if(options["after_submit_insert"] != null){
                _self.after_submit_insert = options["after_submit_insert"];
            }
            if(options["before_submit_insert"] != null){
                _self.before_submit_insert = options["before_submit_insert"];
            }
            if(options["out_valid_rules"] != null){
                _self.out_valid_rules = options["out_valid_rules"];
            }
        }else{
            console.error("Length of object equals zero.");
        }       
    }

    _self.setFuncs = function(obj){
        _self.ofunc = obj;
    }

    _self.bind = function(){        
        _self.form.find(".btns .btn.go").on("click", function(){     
            if(_self.extract_url($(this))){
                _self.submit("add", _self.extract_url($(this)), _self.before_submit_insert);
            }                        
        });     
    }

    _self.unbind = function(){        
        _self.form.find(".btns .btn.go").unbind("click");
    }

    _self.make_message = function(message, callback){
        callback = callback || false;
        clearTimeout(_self.timeout);
        _self.form.find(".warn").fadeIn(500);
        _self.timeout = setTimeout(function(){
            _self.form.find(".warn").fadeOut(500);
            if(typeof callback == "function"){
                callback(_self.form);
            }
        }, 3000);
        _self.form.find(".warn").html(message);        
    }

    _self.extract_url = function(btn_obj){
        if(btn_obj.attr("data-url") != null && btn_obj.attr("data-url") != ""){
            return btn_obj.attr("data-url");
        }
        return false;
    }

    _self.unbind = function(){
        _self.form.find(".btns .btn.add").unbind("click");      
    }

    _self.submit = function(request, _url, _call){
        _call = _call || false;
        _url = _url || false;       
        var v = _self.valid();
        var type = _self.form.attr("data-type");
        if(v[0]){
            var ser = _self.make_array();
            if(typeof _call == "function"){
                _call();
            }
            $.ajax({
                url: _url,
                type: "post",
                data: ser,
                success: function(value){
                    var resp = json(value);
                    if(resp){                     
                        if(request == "add"){
                            if(typeof _self.after_submit_insert == "function"){
                                _self.after_submit_insert(_self, resp);
                            }
                        }                                           
                    }
                }
            }); 
        }else{
            clearTimeout(_self.timeout);
            _self.form.find(".warn").fadeIn(500);
            _self.timeout = setTimeout(function(){
                _self.form.find(".warn").fadeOut(500);
            }, 3000);
            _self.form.find(".warn").html("");
            var f = 0;
            for(var i in v[1]){
                if(f == 0){
                    f++;
                    _self.form.find(".warn").append(v[1][i]);
                }else{
                    _self.form.find(".warn").append("<br>" + v[1][i]);
                }
            }
            
        }
    };

    _self.make_array = function(){

        var stack = {};

        _self.form.find(".txt, input[type=hidden]").each(function(){
            var now = $(this);      
            if(now.hasClass("get")){
                var real = now.closest(".ant-input").find(".get-auto");
                stack[now.attr("name")] = real.val();
            }else{
                stack[now.attr("name")] = now.val();
            }           
        });

        _self.form.find("select").each(function(){
            var now = $(this);                      
            stack[now.attr("name")] = now.val();
        });

        _self.form.find(".radio").each(function(){
            var now = $(this);  
            var _break = false;
            now.find("input[type=radio]").each(function(){
                var radio = $(this);
                if(!_break){
                    if(radio[0].checked){
                        stack[radio.attr("name")] = radio.val();
                        _break = true;
                    }
                }               
            });         
        });

        _self.form.find(".checkbox").each(function(){
            var _tself = $(this);           
            var now = $(this);  
            stack[_tself.attr("name")] = [];
            now.find("input[type=checkbox]").each(function(){
                var checkbox = $(this);             
                if(checkbox.attr("checked") == "checked"){
                    stack[_tself.attr("name")].push(checkbox.val());                    
                }                               
            });         
        });

        return stack;

    };

    _self.valid = function(){
        var valid = true;
        var stack = {};
        _self.form.find(".txt.required").each(function(){
            var now = $(this);                      
            if(now.val() == ""){
                _self.paint(now);
                valid = false;
                stack["empty"] = _self.default_messages["empty"];
            }else{
                _self.normalize(now);
            }
        });

        _self.form.find("select.required").each(function(){
            var now = $(this);                      
            if(now.val() == "" || now.val() == "0" || now.val() == "null"){
                _self.paint(now);
                valid = false;
                stack["select"] = _self.default_messages["select"];
            }else{
                _self.normalize(now);
            }
        });

        _self.form.find(".radio.required").each(function(){
            var now = $(this);      
            var radios = now.find("input[type=radio]").length;
            var check = 0;
            now.find("input[type=radio]").each(function(){
                var radio = $(this);                
                if(!radio[0].checked){
                    check++;
                }
            });                 
            if(check == radios){
                _self.paint(now);
                valid = false;
                stack["radio_check"] = _self.default_messages["radio_check"];
            }else{
                _self.normalize(now);
            }           
        });

        _self.form.find(".checkbox.required").each(function(){
            var now = $(this);      
            var checkboxs = now.find("input[type=checkbox]").length;
            var check = 0;
            now.find("input[type=checkbox]").each(function(){
                var checkbox = $(this);
                if(checkbox.attr("checked") != "checked"){
                    check++;
                }
            });     
            if(check == checkboxs){
                _self.paint(now);
                valid = false;
                stack["check_check"] = _self.default_messages["check_check"];
            }else{
                _self.normalize(now);
            }           
        });

        for(var i in _self.out_valid_rules){
            _self.form.find(i).each(function(){
                var now = $(this);
                var ret = _self.out_valid_rules[i](now);
                if(!ret[0]){
                    _self.paint(now);
                    valid = false;
                    if(ret[1] != null){
                        stack["warn"] = ret[1];
                    }else{
                        stack["warn"] = _self.default_messages["warn"];
                    }                    
                }else{
                    _self.normalize(now);
                }     
            });
        }

        return [valid, stack];

    };

    _self.paint = function(obj){
        if(obj.hasClass("no-border")){
            obj.find(".label").css("color", _self.default_label_error_color);
        }else{
            obj.closest(".ant").css("border", ""+ _self.default_border_width +" solid "+ _self.default_error_border_color +"");
        }       
    };

    _self.normalize = function(obj){
        if(obj.hasClass("no-border")){
            obj.find(".label").css("color", _self.default_label_normal_color);
        }else{
            obj.closest(".ant").css("border", ""+ _self.default_border_width +" solid "+ _self.default_normal_border_color +"");
        }
    };

}

function _clear(){
    if (typeof console._commandLineAPI !== 'undefined') {
        console.API = console._commandLineAPI;
        console._commandLineAPI.clear();
    } else if (typeof console._inspectorCommandLineAPI !== 'undefined') {
        console.API = console._inspectorCommandLineAPI;
        console._inspectorCommandLineAPI.clear();
    } else if (typeof console.clear !== 'undefined') {
        console.API = console;
        console.clear();
    }
}

function rand(min, max) {
    var argc = arguments.length;
    if (argc === 0) {
        min = 0;
        max = 2147483647;
    } else if (argc === 1) {
        throw new Error('Warning: rand() expects exactly 2 parameters, 1 given');
    }
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function tourl(string) {
    var replaces = ["'", '"', "!", "@", "#", "$", "%", "¨", "¢", "¬", "&", "*", "(", ")", "[", "]", "{", "}", "º", "ª", ",", ".", ";", ":", "\\", "/", "¹", "²", "³", "£", "=", "+"];
    for (var i in replaces) {
        while (string.indexOf(replaces[i]) != -1) {
            string = string.replace(replaces[i], "");
        }
    }
    while (string.indexOf(" ") != -1) {
        string = string.replace(" ", "_");
    }
    while (string.indexOf("-") != -1) {
        string = string.replace("-", "_");
    }
    while (string.indexOf("__") != -1) {
        string = string.replace("__", "_");
    }
    return string.toLowerCase();
}

function in_array(needle, haystack) {
    for (var i in haystack) {
        if (haystack[i] === needle) {
            return i;
        }
    }
    return false;
}

function mpop(array) {
    while (array.length > 0) {
        array.pop();
    }
    return array;
}

function mobile() {
    if (navigator.userAgent.match(/Android/i)
            || navigator.userAgent.match(/webOS/i)
            || navigator.userAgent.match(/iPhone/i)
            || navigator.userAgent.match(/iPad/i)
            || navigator.userAgent.match(/iPod/i)
            || navigator.userAgent.match(/BlackBerry/i)
            || navigator.userAgent.match(/Windows Phone/i)
            ) {
        return true;
    }
    else {
        return false;
    }
}

function json(string){
    try{
        var conv = $.parseJSON(string);
        return conv;
    }catch(e){
        if(string != ""){
            console.log(string);
        }
        return false;
    }   
}

function decode_array(array){
    for(i in array){
        array[i] = utf8_decode(array[i]);
    }
    return array;
}

function utf8_encode(argString) {

  if (argString === null || typeof argString === 'undefined') {
    return '';
  }

  var string = (argString + '');
  var utftext = '',
    start, end, stringl = 0;

  start = end = 0;
  stringl = string.length;
  for (var n = 0; n < stringl; n++) {
    var c1 = string.charCodeAt(n);
    var enc = null;

    if (c1 < 128) {
      end++;
    } else if (c1 > 127 && c1 < 2048) {
      enc = String.fromCharCode(
        (c1 >> 6) | 192, (c1 & 63) | 128
      );
    } else if ((c1 & 0xF800) != 0xD800) {
      enc = String.fromCharCode(
        (c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
      );
    } else { // surrogate pairs
      if ((c1 & 0xFC00) != 0xD800) {
        throw new RangeError('Unmatched trail surrogate at ' + n);
      }
      var c2 = string.charCodeAt(++n);
      if ((c2 & 0xFC00) != 0xDC00) {
        throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
      }
      c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
      enc = String.fromCharCode(
        (c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
      );
    }
    if (enc !== null) {
      if (end > start) {
        utftext += string.slice(start, end);
      }
      utftext += enc;
      start = end = n + 1;
    }
  }

  if (end > start) {
    utftext += string.slice(start, stringl);
  }
  return utftext;
}

function utf8_decode(str_data) {
  var tmp_arr = [],
    i = 0,
    ac = 0,
    c1 = 0,
    c2 = 0,
    c3 = 0,
    c4 = 0;
    str_data += '';
    while (i < str_data.length) {
        c1 = str_data.charCodeAt(i);
        if (c1 <= 191) {
            tmp_arr[ac++] = String.fromCharCode(c1);
            i++;
        } else if (c1 <= 223) {
            c2 = str_data.charCodeAt(i + 1);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
            i += 2;
        } else if (c1 <= 239) {      
            c2 = str_data.charCodeAt(i + 1);
            c3 = str_data.charCodeAt(i + 2);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        } else {
            c2 = str_data.charCodeAt(i + 1);
            c3 = str_data.charCodeAt(i + 2);
            c4 = str_data.charCodeAt(i + 3);
            c1 = ((c1 & 7) << 18) | ((c2 & 63) << 12) | ((c3 & 63) << 6) | (c4 & 63);
            c1 -= 0x10000;
            tmp_arr[ac++] = String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF));
            tmp_arr[ac++] = String.fromCharCode(0xDC00 | (c1 & 0x3FF));
            i += 4;
        }
    }
    return tmp_arr.join('');
}
