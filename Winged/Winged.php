<?php

namespace Winged;

use Winged\Buffer\Buffer;
use Winged\Controller\Controller;
use Winged\Error\Error;
use Winged\File\File;
use Winged\Route\RouteExec;
use Winged\Utils\WingedLib;
use Winged\Utils\Container;

WingedHead::init();

/**
 * This class its a main class of Winged
 * @version 1.8.3.5
 * @access public static object
 * @author Matheus Prado Rodrigues
 * @copyright (c) 2017, Winged Framework
 */
class Winged
{

    public static $standard;
    public static $standard_controller;
    public static $controller_page;
    public static $controller_action;
    public static $controller_debug = true;
    public static $http;
    public static $https;
    public static $protocol;
    public static $uri = false;
    public static $pure_uri = false;
    public static $page;
    public static $parent = false;
    public static $params = [];
    public static $oparams = [];
    public static $controller_params = [];
    public static $key;
    public static $page_surname;
    public static $routed_file;
    public static $router = 1;
    public static $routes = [];
    public static $restful = false;
    public static $route_dir;

    /**
     * @var $controller Controller
     */
    public static $controller;
    public static $geted_file;
    public static $reset;
    public static $notfound;
    public static $is_standard = false;
    public static $ob_buffer = false;

    /**
     * @access public
     * @example Winged::start() this method starts all of winged, don't call this method again.
     * @return void
     */
    public static function start()
    {
        if (!self::$controller) {
            self::$controller = new Controller();
        }
        if (is_null(WingedConfig::$NOTFOUND) || !WingedConfig::$NOTFOUND) {
            WingedConfig::$NOTFOUND = "./winged/class/rewrite/error/404.php";
        }
        self::$controller_debug = (WingedConfig::$CONTROLLER_DEBUG !== null) ? WingedConfig::$CONTROLLER_DEBUG : true;
        if (is_null(WingedConfig::$STANDARD)) {
            self::$standard = WingedConfig::$STANDARD;
            self::$notfound = WingedConfig::$NOTFOUND;
        } else {
            self::$notfound = WingedConfig::$NOTFOUND;
            self::$standard = WingedConfig::$STANDARD;
            self::$standard_controller = WingedConfig::$STANDARD_CONTROLLER;
            self::$router = WingedConfig::$ROUTER;
        }
        self::nosplit();
    }

    public static function nosplit()
    {
        self::normalize();
        $dirs = self::getdir(WingedLib::dotslash(self::$uri));
        $page = trim($dirs["page"]);
        $parent = WingedLib::dotslash(WingedLib::dotslash(trim($dirs["parent"])), true);
        $params = $dirs["params"];

        self::$key = $parent . $page . "/";

        self::$page_surname = $page;
        self::$page = $page;
        self::$parent = $parent;
        self::$params = $params;
        self::$controller_params = $params;

        $vect = self::return_path_route();

        self::$page = $vect["page"];
        self::$routed_file = DOCUMENT_ROOT . str_replace('./', '', $vect["file"]);
        self::$route_dir = DOCUMENT_ROOT . str_replace('./', '', $vect["dir"]);

        $controller_info = self::controller_info();

        self::$controller_page = $controller_info['controller'];
        self::$controller_action = $controller_info['action'];

        $before = false;
        if (Container::$self->methodExists('beforeSearchController')) {
            $before = Container::$self->beforeSearchController();
        }

        if ($before === false || $before === null) {
            $before = false;
            $found = self::$controller->find();
            if (!$found) {
                if (Container::$self->methodExists('whenControllerNotFound')) {
                    $before = Container::$self->whenControllerNotFound();
                }
                if (file_exists(self::$route_dir) && is_directory(self::$route_dir)) {
                    if (file_exists(self::$routed_file)) {
                        include_once self::$routed_file;
                        RouteExec::execute();
                    }
                }
                if ($before === false || $before === null) {
                    RouteExec::sendErrorResponse();
                }
                $file = new File(WingedConfig::$NOTFOUND, false);
                if ($file->exists()) {
                    Buffer::reset();
                    include_once WingedConfig::$NOTFOUND;
                    Buffer::flushKill();
                    exit;
                } else {
                    Error::_die('END_OF_EXECUTION', 'Nothing exists. Controller no exists, action no exists, routes not found and [Not found] file not exists.', __LINE__, __FILE__, __LINE__);

                }
            }
        }

    }

    private static function controller_info()
    {
        $exp = WingedLib::slashexplode(Winged::$parent);
        $uri = WingedLib::slashexplode(self::$uri);
        $nar = [];

        if (isset($exp[0]) && $exp[0] == '') {
            return ['controller' => self::$standard_controller, 'action' => 'index'];
        } else {
            for ($i = 0; $i < count7($uri); $i++) {
                if (!in_array($uri[$i], $exp)) {
                    $nar[] = $uri[$i];
                }
            }
            if (count7($nar) == 0) {
                return ['controller' => self::$standard_controller, 'action' => 'index'];
            } else if (count7($nar) == 1) {
                return ['controller' => $nar[0], 'action' => 'index'];
            } else {
                return ['controller' => $nar[0], 'action' => $nar[1]];
            }
        }
    }

    /**
     * normalize uri and ignore domain name and al folder before root of application
     */
    public static function normalize()
    {

        $b_uri = server("request_uri");
        $free_get = explode("?", $b_uri);
        $uri = WingedLib::convertslash($free_get[0]);
        $self = WingedLib::convertslash(server("php_self"));
        $host = WingedLib::convertslash(server("server_name"));
        $uris = WingedLib::slashexplode($uri);
        $selfs = WingedLib::slashexplode($self);
        $self = "";
        $lastself = "";
        for ($x = 0; $x < count7($selfs); $x++) {
            if ($selfs[$x] != "index.php") {
                if ($x == 0) {
                    $self = $selfs[$x];
                } else {
                    $self .= "/" . $selfs[$x];
                }
                $lastself = $selfs[$x];
            }
        }
        $fix = false;
        $cont = 0;
        $find = 0;
        $inarray = [];
        for ($x = 0; $x < count7($uris); $x++) {
            if ($uris[$x] == $lastself || $lastself == "") {
                $fix = true;
                array_push($inarray, $lastself);
            }
            $str_count = count7($inarray);
            if (($fix && $uris[$x] != $lastself) || ($fix && $str_count >= 2)) {
                if ($cont == 0) {
                    $uri = "./" . $uris[$x];
                    $cont++;
                } else {
                    $uri .= "/" . $uris[$x];
                }
                $find++;
            }
        }
        if ($find == 0) {
            $uri = ".";
        }
        $uri .= "/";
        if ($self == "") {
            $https = "https://" . $host . "/";
            $http = "http://" . $host . "/";
        } else {
            $https = "https://" . $host . "/" . $self . "/";
            $http = "http://" . $host . "/" . $self . "/";
        }
        self::$uri = $uri;
        if (count7($free_get) > 1) {
            self::$pure_uri = $uri . '?' . $free_get[1];
        } else {
            self::$pure_uri = $uri;
        }
        self::$https = $https;
        self::$http = $http;
        if (server('https')) {
            if (server('https') != 'off') {
                self::$protocol = $https;
            } else {
                self::$protocol = $http;
            }
        } else {
            self::$protocol = $http;
        }
    }

    /**
     * return page name or controler name, current dir of application and params founded in uri
     * @param $uri
     * @param bool $extra_dir
     * @return array
     */
    private static function getdir($uri, $extra_dir = false)
    {
        $exp = WingedLib::slashexplode($uri);
        $dir = '';
        if ($extra_dir) {
            $dir .= './' . $extra_dir . '/';
        }
        if (count7($exp) > 0) {
            $x = 0;
            if ($dir == '') {
                $dir .= WingedLib::dotslash($exp[$x], true);
            } else {
                $dir .= $exp[$x] . '/';
            }
            if (is_directory($dir)) {
                unset($exp[$x]);
            } else {
                if ($dir == '') {
                    $dir .= "./";
                } else {
                    $dir = './' . $extra_dir . '/';
                }
            }
            foreach ($exp as $key => $value) {
                $ant = $dir;
                if ($x == 0) {
                    $dir .= $value;
                } else {
                    $dir .= "/" . $value;
                }
                if (is_directory($dir)) {
                    unset($exp[$key]);
                } else {
                    $dir = $ant;
                    break;
                }
                $x++;
            }
            if (count7($exp) == 0) {
                self::$is_standard = true;
                return [
                    "page" => self::$standard,
                    "parent" => $dir,
                    "params" => false
                ];
            } else {
                $exp = WingedLib::resetarray($exp);
                $page = $exp[0];
                unset($exp[0]);
                $params = [];
                foreach ($exp as $key => $value) {
                    array_push($params, $value);
                }
                return [
                    "page" => $page,
                    "parent" => $dir,
                    "params" => $params
                ];
            }
        }
        self::$is_standard = true;
        return [
            "page" => self::$standard,
            "parent" => "./",
            "params" => false
        ];
    }

    public static function return_path_route()
    {
        $parent = self::$parent;
        $router = self::$router;
        $page = self::$page;
        if (is_null($router)) {
            $router = 1;
        }
        switch ($router) {
            case 1:
                return [
                    "file" => $parent . "routes/" . $page . ".php",
                    "dir" => $parent . "routes/",
                    "page" => $page
                ];
                break;

            case 2:
                return [
                    "file" => "./routes/" . $page . ".php",
                    "dir" => "./routes/",
                    "page" => $page
                ];
                break;

            case 3:
                return [
                    "file" => $parent . "routes/routes.php",
                    "dir" => $parent . "routes/",
                    "page" => "routes"
                ];
                break;

            default:
                return [
                    "file" => "./routes/routes.php",
                    "dir" => "./routes/",
                    "page" => "routes"
                ];
                break;
        }
    }

    public static function post()
    {
        if ($_POST) {
            return $_POST;
        }
        return [];
    }

    public static function get()
    {
        if ($_GET) {
            return $_GET;
        }
        return [];
    }

    public static function initialJs()
    {
        return '<script>
                    window.protocol = "' . Winged::$protocol . '"; 
                    window.parent = "' . Winged::$parent . '"; 
                    window.page_surname = "' . Winged::$page_surname . '"; 
                    window.uri = "' . Winged::$uri . '"; 
                    window.controller_params = JSON.parse(\'' . json_encode(Winged::$controller_params) . '\'); 
                    window.controller_action = "' . Winged::$controller_action . '";                
                </script>';
    }
}