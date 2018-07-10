<?php

namespace Winged\Restful;

use Winged\Error\Error;
use Winged\Utils\WingedLib;
use Winged\Winged;

class Restful
{
    private $rests = [];
    public static $rest = false;
    private $actions = [];

    public function restful_page()
    {
        $page = Winged::$page;
        $page_key = Winged::$key;
        $parent = Winged::$parent;
        $routed_file = Winged::$routed_file;
        $route_dir = Winged::$route_dir;

        if (file_exists($route_dir) && is_directory($route_dir)) {
            if (file_exists($routed_file)) {
                include_once($routed_file);
                $vect = $this->findKey($page_key);
                if ($vect) {
                    $active_method = $vect["method"];
                    $path = $vect["class_path"];
                    $class_name = $vect["class_name"];
                    $function = $vect["function"];
                    $construct = false;
                    $extra = [];

                    if (array_key_exists("construct", $vect)) {
                        $construct = $vect["construct"];
                    }

                    if (array_key_exists("extra", $vect)) {
                        $extra = $vect["extra"];
                    }

                    $x = 0;
                    $array = [];

                    if (count7(Winged::$params) > 0 && gettype(Winged::$params) == "array") {
                        foreach ($vect as $key => $value) {
                            if (is_int($key)) {
                                $param = Winged::$params[$x];
                                if (gettype($value) == "object" && get_class($value) == "Rest") {
                                    if ($value->rule) {
                                        if (!preg_match("/" . $value->rule . "/", $param) && $value->rule != 'no-rule') {
                                            Error::_die(Error::$FRAMEWORK_RULE, "the parameter '" . $param . "' failed by the rule '" . $value->rule . "'", __LINE__, __FILE__, __LINE__);
                                        }
                                    }
                                    if (array_key_exists($value->name, $array)) {
                                        Error::_die(Error::$FRAMEWORK_RULE, "the nickname for the parameters was doubled. duplicate nickname '" . $value->name . "'", __LINE__, __FILE__, __LINE__);
                                    } else {
                                        $array[$value->name] = $param;
                                    }
                                    $x++;
                                    if ($x >= count7(Winged::$params)) {
                                        break;
                                    }
                                } else {
                                    Error::_die(Error::$FRAMEWORK_RULE, "the value found during the looping is not a type 'Rest' object.", __LINE__, __FILE__, __LINE__);
                                }
                            }
                        }
                    }

                    Winged::$oparams = Winged::$params;
                    Winged::$params = $array;

                    if (count7($array) > 0) {
                        if ($vect[count7($array) - 1]->method) {
                            $function = $vect[count7($array) - 1]->method;
                        }
                        if ($vect[count7($array) - 1]->class_path) {
                            $path = $vect[count7($array) - 1]->class_path;
                        }
                        if ($vect[count7($array) - 1]->class_name) {
                            $class_name = $vect[count7($array) - 1]->class_name;
                        }
                        if ($vect[count7($array) - 1]->construct) {
                            $construct = $vect[count7($array) - 1]->construct;
                        }
                        if ($vect[count7($array) - 1]->extra) {
                            $extra = $vect[count7($array) - 1]->extra;
                        }
                    }

                    Winged::$geted_file = false;

                    if (file_exists($path) && !is_directory($path)) {
                        include_once($path);
                        if (class_exists($class_name)) {
                            if (array_key_exists("loads", $extra)) {
                                foreach ($extra["loads"] as $key => $load) {
                                    if (file_exists($load) && !is_directory($load)) {
                                        include_once $load;
                                    } else {
                                        Error::_die(Error::$FRAMEWORK_RULE, "include_once fails for expression {$load}", __LINE__, __FILE__, __LINE__);
                                    }
                                }
                            }
                            if (array_key_exists("vars", $extra)) {
                                foreach ($extra["vars"] as $key => $var) {
                                    ${$key} = $var;
                                }
                            }
                            if ($construct && is_array($construct)) {
                                $reflection = new \ReflectionClass($class_name);
                                $obj = $reflection->newInstanceArgs($construct);
                            } else {
                                $obj = new $class_name();
                            }
                            if (method_exists($obj, $function)) {
                                self::$rest = $vect;
                                $reflect = new \ReflectionMethod($class_name, $function);
                                $apply = $reflect->getParameters();
                                $num = 0;
                                if (!empty($apply)) {
                                    $apply = $apply[0];
                                    foreach ($apply as $key => $value) {
                                        $num++;
                                    }
                                }
                                if (count7(Winged::$oparams) >= $num) {
                                    $the_params = Winged::$oparams;
                                    if (array_key_exists("extra_params", $extra) && is_array($extra["extra_params"])) {
                                        array_push($the_params, $extra["extra_params"]);
                                    }
                                    $ret = $reflect->invokeArgs($obj, $the_params);
                                    if (gettype($ret) != null && gettype($ret) == "array") {
                                        echo json_encode($ret);
                                    }
                                } else {
                                    Error::_die(Error::$FRAMEWORK_RULE, "the number of arguments to the method (" . $function . ") '" . $num . "'  is greater than that found in the url '" . count7(Winged::$oparams) . "'", __LINE__, __FILE__, __LINE__);
                                }
                            } else {
                                Error::_die(Error::$FRAMEWORK_RULE, "method '" . $function . "' no exists on class '" . $class_name . "'", __LINE__, __FILE__, __LINE__);
                            }
                        } else {
                            Error::_die(Error::$FRAMEWORK_RULE, "class '" . $class_name . "' no exists on path '" . $path . "'", __LINE__, __FILE__, __LINE__);
                        }
                    } else {
                        Error::_die(Error::$FRAMEWORK_RULE, "path '" . $path . "' no exists", __LINE__, __FILE__, __LINE__);
                    }
                } else {
                    Error::_die(Error::$FRAMEWORK_RULE, "no rest was found in the file " . $routed_file, __LINE__, __FILE__, __LINE__);
                }
            } else {
                Error::_die("file '" . $page . ".php' not fount in '" . $route_dir . "'", __CLASS__ . ' : ' . __LINE__, __FILE__, __LINE__);
            }
        } else {
            Error::_die("folder 'routes' not fount in '" . $parent . "'", __CLASS__ . ' : ' . __LINE__, __FILE__, __LINE__);
        }
    }


    private function findKey($okey)
    {
        pre_clear_buffer_die($okey);
        $okey = trim($okey);
        $found = false;
        $f_num = 0;
        $stack = [];
        foreach ($this->rests as $key => $value) {
            $key = trim($key);
            $exp = explode("/", $key);
            $index = WingedLib::dotslash(WingedLib::dotslash($exp[1]), true);
            $pos = stripos($key, $okey);
            if (is_int($pos) && !$found) {
                $replace = str_replace($okey, "", $key);
                $found = ["key" => $key, "value" => $value, "uri_comp" => WingedLib::dotslash($replace)];
                $value["uri_comp"] = WingedLib::dotslash($replace);
                $stack[$key] = $value;
                $f_num++;
            } else if (is_int($pos)) {
                $replace = str_replace($okey, "", $key);
                $value["uri_comp"] = WingedLib::dotslash($replace);
                $stack[$key] = $value;
                $f_num++;
            }
        }
        if ($f_num > 1) {
            return $this->corrertRoute($stack);
        } else if ($f_num == 0) {
            return false;
        } else {
            $this->actions = [];
            $newparams = [];
            $exp = [];
            if ($found["uri_comp"] != "") {
                $exp = explode("/", $found["uri_comp"]);
            }
            $params = Winged::$params;
            if (count7($params) == count7($exp) || Winged::$is_standard) {
                $init = true;
                for ($x = 0; $x < count7($exp); $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if (@count7($math) == 2 && trim($math[0]) == "action") {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }
                    if (!preg_match("/" . $preg . "/", $params[$x]) && $preg != 'no-rule') {
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
            Error::_die(Error::$FRAMEWORK_RULE, "the URL passed as reference for restful call was recognized by a route, but the comparison of the parameters count found in the URL and the parameters count configured for the route are different. Make a new call using the correct number of parameters in the URL.", __LINE__, __FILE__, __LINE__);
            return false;
        }

    }

    /**
     * find corret route for the call
     * @param $probable_routes
     * @return bool
     */
    private function corrertRoute($probable_routes)
    {
        foreach ($probable_routes as $key => $value) {
            $this->actions = [];
            $exp = [];
            if ($value["uri_comp"] != "") {
                $exp = explode("/", $value["uri_comp"]);
            }

            $params = Winged::$params;

            if (!$params) {
                $params = [];
            }

            $init = true;
            if (count7($params) == count7($exp)) {
                for ($x = 0; $x < count7($exp); $x++) {
                    $math = explode(":", $exp[$x]);
                    $is_action = false;
                    if (count7($math) == 2 && $math[0] == "action") {
                        $preg = trim($math[1]);
                        $is_action = true;
                    } else {
                        $preg = $exp[$x];
                    }

                    if (!preg_match("/^" . $preg . "$/", $params[$x]) && $preg != 'no-rule') {
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
                $newparams = [];
                foreach ($params as $now) {
                    $newparams[] = $now;
                }
                Winged::$params = $newparams;
                return $value;
            }

        }
        return false;
    }

    /**
     * add a route for restful calls
     * @param $index
     * @param $rest
     */
    public function addRest($index, $rest)
    {
        $index = WingedLib::dotslash(WingedLib::dotslash($index), true);
        if (!array_key_exists($index, $this->rests)) {
            $this->rests[$index] = $rest;
        } else {
            Error::_die(Error::$FRAMEWORK_RULE, "Rest '" . $index . "' in file path '" . Winged::$routed_file . "' was doubled.", __LINE__, __FILE__, __LINE__);
        }
    }

}