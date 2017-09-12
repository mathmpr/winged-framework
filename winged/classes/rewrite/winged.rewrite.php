<?php

class Rewrite
{

    private $routes = array();
    private $actions = array();
    public $find_vect = false;
    public $rewrite_reset = false;

    public function rewrite_page()
    {
        if (!$this->reset()) {

            $page = Winged::$page;
            $page_key = Winged::$key;
            $parent = Winged::$parent;
            $routed_file = Winged::$routed_file;
            $route_dir = Winged::$route_dir;

            if (file_exists($route_dir) && is_directory($route_dir)) {

                if (file_exists($routed_file)) {

                    include_once($routed_file);

                    if (!Winged::$restful) {
                        $vect = $this->find_key($page_key);
                        $find = '';
                        $x = 0;
                        $array = array();
                        if ($vect) {
                            if (array_key_exists('index', $vect)) {
                                $find = $vect["index"];
                            }
                            if (count(Winged::$params) > 0 && gettype(Winged::$params) == "array") {
                                foreach ($vect as $key => $value) {
                                    if (is_int($key)) {
                                        $param = Winged::$params[$x];
                                        if (gettype($value) == "object" && get_class($value) == "Parameter") {
                                            if (array_key_exists($value->name, $array)) {
                                                Winged::error("the nickname for the parameters was doubled. duplicate nickname '" . $value->name . "'");
                                            } else {
                                                $array[$value->name] = $param;
                                            }
                                            $x++;
                                            if ($x >= count(Winged::$params)) {
                                                break;
                                            }
                                        } else {
                                            Winged::error("the value found during the looping is not a type 'Parameter' object.");
                                        }
                                    }
                                }
                            }

                            Winged::$oparams = Winged::$params;
                            if (count($array) > 0) {
                                Winged::$params = $array;
                            }


                            if (count($array) > 0) {
                                if ($vect[count($array) - 1]->index) {
                                    $find = $vect[count($array) - 1]->index;
                                }
                            }

                            Winged::$geted_file = $find;

                            if (file_exists($find) && !is_directory($find) && !array_key_exists('controller', $vect)) {
                                if (array_key_exists("extra", $vect)) {
                                    $extra = $vect["extra"];
                                    if (array_key_exists("loads", $extra)) {
                                        foreach ($extra["loads"] as $key => $load) {
                                            if (file_exists($load) && !is_directory($load)) {
                                                include_once $load;
                                            } else {
                                                $warn = Winged::push_warning(__CLASS__, "include_once fails for expression {$load}", true);
                                                winged_error_handler("8", $warn["error_description"], "(logic error)", "in class : " . __LINE__, $warn["real_backtrace"]);
                                                Winged::get_errors(__LINE__, __FILE__);
                                            }
                                        }
                                    }
                                    if (array_key_exists("vars", $extra)) {
                                        foreach ($extra["vars"] as $key => $var) {
                                            ${$key} = $var;
                                        }
                                    }
                                }
                                include_once $find;
                                if (Winged::error_exists()) {
                                    Winged::get_errors(__LINE__, __FILE__);
                                }
                            } else {
                                if (array_key_exists('controller', $vect)) {
                                    if (check_all_keys(['name', 'action'], $vect['controller'])) {
                                        $this->find_vect = $vect['controller'];
                                    }
                                }

                                $have_action = false;

                                if ($this->find_vect) {
                                    $have_action = Winged::$controller->allExists($this->find_vect);
                                }

                                if (WingedConfig::$FORCE_NOTFOUND && !$have_action) {
                                    $this->setDefault404();
                                } else {

                                    Winged::$controller_action = $this->find_vect['action'];
                                    Winged::$controller_page = $this->find_vect['name'];
                                    Winged::$controller->find();

                                    /*
                                    $error = true;
                                    if (array_key_exists('controller', $vect)) {
                                        if (check_all_keys(['name', 'action'], $vect['controller'])) {
                                            if (Winged::$controller->allExists($vect['controller']) && Winged::$controller->error_level >= 1) {
                                                $error = false;
                                            }
                                        }
                                    }
                                    if ($error) {
                                        Winged::convert_warnings_into_erros();
                                        Winged::get_errors(__LINE__, __FILE__);
                                    }
                                    */

                                }
                            }
                        } else {
                            if (WingedConfig::$FORCE_NOTFOUND) {
                                $this->setDefault404();
                            } else {
                                Winged::push_warning(__CLASS__, "no route was found in the file " . $routed_file, true);
                                Winged::convert_warnings_into_erros();
                                Winged::get_errors(__LINE__, __FILE__);
                            }
                        }
                    } else {
                        Winged::$restful_obj->restful_page();
                    }
                } else {
                    Winged::push_warning(__CLASS__, "file '" . $page . ".php' not fount in '" . $route_dir . "'", true);
                    Winged::convert_warnings_into_erros();
                    Winged::get_errors(__LINE__, __FILE__);
                }
            } else {
                Winged::push_warning(__CLASS__, "folder 'routes' not fount in '" . $parent . "'", true);
                Winged::convert_warnings_into_erros();
                Winged::get_errors(__LINE__, __FILE__);
            }
        }
    }

    public function setDefault404()
    {
        if (file_exists(Winged::$notfound) && !is_directory(Winged::$notfound)) {
            Winged::obReset();
            include_once Winged::$notfound;
            Winged::obShowFinish();
            if (Winged::error_exists()) {
                Winged::get_errors(__LINE__, __FILE__);
            }
        } else {
            Winged::push_warning(__CLASS__, "the error file 404 was not found in '" . Winged::$notfound . "'. this is very very bad", true);
            Winged::convert_warnings_into_erros();
            Winged::get_errors(__LINE__, __FILE__);
        }
    }


    private function reset()
    {
        $parent = Winged::$parent;
        $outher_conf = $parent . "config/config.php";
        if (file_exists($outher_conf) && !is_directory($outher_conf) && !$this->rewrite_reset) {
            include_once $outher_conf;
            mb_internal_encoding(WingedConfig::$INTERNAL_ENCODING);
            mb_http_output(WingedConfig::$OUTPUT_ENCODING);
            Winged::clear_warnings();
            Winged::clear_errors();
            $this->rewrite_reset = true;
            Winged::start();
            return true;
        }
        return false;
    }

    public function find_key($okey)
    {
        $okey = trim($okey);
        $found = false;
        $f_num = 0;
        $stack = array();
        foreach ($this->routes as $key => $value) {
            $key = trim($key);
            $exp = explode("/", $key);
            $pos = stripos($key, $okey);
            if (count($exp) >= 3) {
                if (is_int($pos) && !$found) {
                    $replace = str_replace($okey, "", $key);
                    $found = array("key" => $key, "value" => $value, "uri_comp" => wl::dotslash($replace));
                    $point = wl::dotslash($replace);
                    if($point == '.'){
                        $value["uri_comp"] = false;
                    }else{
                        $value["uri_comp"] = wl::dotslash(wl::dotslash($replace));
                    }
                    $stack[$key] = $value;
                    $f_num++;
                } else if (is_int($pos)) {
                    $replace = str_replace($okey, "", $key);
                    $value["uri_comp"] = wl::dotslash($replace);
                    $stack[$key] = $value;
                    $f_num++;
                }
            }
        }

        if ($f_num > 1) {
            return $this->corrert_route($stack);
        } else if ($f_num == 0) {
            return false;
        } else {
            $this->actions = array();
            $newparams = array();
            $exp = false;
            if ($found["uri_comp"] != "" && $found["uri_comp"] != ".") {
                $exp = explode("/", $found["uri_comp"]);
            }
            $params = Winged::$params;
            if(is_bool($params)){
                $params = [];
            }
            if (count($params) == count($exp) || Winged::$is_standard || $exp == false) {
                $init = true;
                for ($x = 0; $x < count($exp); $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if (@count($math) == 2 && trim($math[0]) == "action") {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }
                    if(array_key_exists($x, $params)){
                        if (!preg_match("/^" . $preg . "$/", $params[$x]) && $preg != 'no-rule') {
                            $warn = Winged::push_warning(__CLASS__, "{$params[$x]} was reproved by rule {$preg}", true);
                            winged_error_handler("8", $warn["error_description"], "(logic error)", "in class : " . __LINE__, $warn["real_backtrace"]);
                            Winged::get_errors(__LINE__, __FILE__);
                            $init = false;
                            break;
                        }
                    }
                    if ($is_action) {
                        $this->actions[] = $preg;
                        $search = array_search($preg, $params);
                        if (is_int($search)) {
                            unset($params[$search]);
                        }
                    }
                }

                if (is_array($params)) {
                    foreach ($params as $key => $now) {
                        $newparams[] = $now;
                    }
                }

                if ($init) {
                    Winged::$params = $newparams;
                    return $found["value"];
                }
            }
            Winged::error("the URL passed as reference for call was recognized by a route file, but the comparison of the parameters count found in the URL and the parameters count configured for the route are different. Make a new call using the correct number of parameters in the URL.");
            return false;
        }

    }

    private function corrert_route($probable_routes)
    {
        foreach ($probable_routes as $key => $value) {
            $this->actions = array();
            $exp = array();
            if ($value["uri_comp"] != "") {
                $exp = explode("/", $value["uri_comp"]);
            }
            $params = Winged::$params;

            if (!$params) {
                $params = array();
            }

            $init = true;

            if (count($params) == count($exp)) {
                for ($x = 0; $x < count($exp); $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if (count($math) == 2 && $math[0] == "action") {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }

                    if (!preg_match("/^" . $preg . "$/", $params[$x]) && $preg != 'no-rule') {
                        $warn = Winged::push_warning(__CLASS__, "{$params[$x]} was reproved by rule {$preg}", true);
                        winged_error_handler("8", $warn["error_description"], "(logic error)", "in class : " . __LINE__, $warn["real_backtrace"]);
                        Winged::get_errors(__LINE__, __FILE__);
                        $init = false;
                        break;
                    }

                    if ($is_action) {
                        $this->actions[] = $preg;
                        $search = array_search($preg, $params);
                        if (is_int($search)) {
                            unset($params[$search]);
                        }
                    }
                }
            } else {
                $init = false;
            }

            if ($init) {
                $newparams = array();
                foreach ($params as $key => $now) {
                    $newparams[] = $now;
                }
                Winged::$params = $newparams;
                return $value;
            }

        }

        return false;

    }

    public function get_actions_count()
    {
        return count($this->actions);
    }

    public function get_actions()
    {
        return $this->actions;
    }

    public function addroute($index, $route)
    {
        $index = wl::dotslash(wl::dotslash($index), true);
        if (!array_key_exists($index, $this->routes)) {
            $this->routes[$index] = $route;
        } else {
            Winged::error("Route '" . $index . "' in file path '" . Winged::$routed_file . "' was doubled.");
        }
    }

    public function render($path, $vars = array(), $loads = array())
    {
        if (file_exists($path) && !is_directory($path)) {
            foreach ($vars as $key => $value) {
                if (!is_int($key) && is_string($key)) {
                    ${$key} = $value;
                }
            }
            foreach ($vars as $key => $value) {
                if (!is_int($key) && is_string($key)) {
                    ${$key} = $value;
                }
            }
            foreach ($loads as $key => $value) {
                if (file_exists($value) && !is_directory($value)) {
                    include_once $value;
                }
            }
            require $path;
        } else {
            Winged::push_warning(__CLASS__, "file {$path} can't rendred because file not found.");
        }
    }

}