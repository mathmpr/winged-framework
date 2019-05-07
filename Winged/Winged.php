<?php

namespace Winged;

use Winged\Buffer\Buffer;
use Winged\Controller\Controller;
use Winged\Error\Error;
use Winged\File\File;
use Winged\Http\HttpResponseHandler;
use Winged\Http\Session;
use Winged\Route\Route;
use Winged\Route\RouteExec;
use Winged\Utils\WingedLib;
use Winged\Utils\Container;
use WingedConfig;

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
    public static $http_parent;
    public static $https_parent;
    public static $protocol_parent;
    public static $full_url_provider;
    public static $free_get;
    public static $host;
    public static $have_www_in_request = false;
    public static $have_https_protocol_in_request = false;

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
    public static $router_dir;

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

        self::$have_https_protocol_in_request = false;
        self::$have_www_in_request = false;

        if (!self::$controller) {
            self::$controller = new Controller();
        }
        if (is_null(WingedConfig::$config->NOTFOUND) || !WingedConfig::$config->NOTFOUND) {
            WingedConfig::$config->NOTFOUND = "./winged/class/rewrite/error/404.php";
        }
        self::$controller_debug = (WingedConfig::$config->CONTROLLER_DEBUG !== null) ? WingedConfig::$config->CONTROLLER_DEBUG : true;
        if (is_null(WingedConfig::$config->STANDARD)) {
            self::$standard = WingedConfig::$config->STANDARD;
            self::$notfound = WingedConfig::$config->NOTFOUND;
        } else {
            self::$notfound = WingedConfig::$config->NOTFOUND;
            self::$standard = WingedConfig::$config->STANDARD;
            self::$standard_controller = WingedConfig::$config->STANDARD_CONTROLLER;
            self::$router = WingedConfig::$config->ROUTER;
        }
        self::nosplit();
    }

    public static function clear_urls()
    {
        $refers = [
            & self::$http,
            & self::$https,
            & self::$protocol,
            & self::$http_parent,
            & self::$https_parent,
            & self::$protocol_parent,
            & self::$full_url_provider,
            & self::$free_get,
            & self::$host,
        ];

        foreach ($refers as &$ref) {
            $ref = str_replace(['http://', 'https://', '//', 'http:~~', 'https:~~'], ['http:~~', 'https:~~', '/', 'http://', 'https://'], $ref);
        }

    }

    public static function nosplit()
    {
        self::normalize();
        $dirs = self::getdir();
        self::clear_urls();

        $page = trim($dirs["page"]);

        self::$page_surname = $page;
        self::$page = $page;
        self::$params = $dirs["params"];
        self::$controller_params = $dirs["params"];

        $vect = self::return_path_route();

        self::$page = $vect["page"];
        self::$routed_file = DOCUMENT_ROOT . WingedLib::clearPath($vect["file"]);
        self::$router_dir = DOCUMENT_ROOT . WingedLib::clearPath($vect["dir"]) . '/';

        $controller_info = self::controller_info();

        self::$controller_page = $controller_info['controller'];
        self::$controller_action = $controller_info['action'];

        $force_www = false;
        $force_https = false;


        if (WingedConfig::$config->FORCE_WWW === true) {
            $force_www = true;
        }

        if (WingedConfig::$config->FORCE_HTTPS === true) {
            $force_https = true;
        }

        $location = false;

        if (($force_https === true && !self::$have_https_protocol_in_request) && ($force_www === true && !self::$have_www_in_request)) {
            $location = str_replace('http://', 'https://www.', self::$full_url_provider);
        } else if ($force_https === true && !self::$have_https_protocol_in_request) {
            $location = str_replace('http://', 'https://', self::$full_url_provider);
        } else if ($force_www === true && !self::$have_www_in_request) {
            if (self::$have_https_protocol_in_request) {
                $location = str_replace('https://', 'https://www.', self::$full_url_provider);
            } else {
                $location = str_replace('http://', 'http://www.', self::$full_url_provider);
            }
            if($location[strlen($location) - 2] === '/' && $location[strlen($location) - 1] === '/'){
                $location = substr_replace($location, '', (strlen($location) - 1), 1);
            }
        }

        if ($location) {
            if (is_post()) {
                Session::set('__WINGED_POST_REDIRECT__', $_POST);
            }
            $headers = getallheaders();
            Session::set('__WINGED_METHOD_REDIRECT__', server('request_method'));
            Session::set('__WINGED_HEADERS_REDIRECT__', $headers);
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $location);
            exit;
        }


        $headers = getallheaders();
        $gzip_accept = false;
        $use_gzencode = false;
        if (array_key_exists('Accept-Encoding', $headers)) {
            if (is_int(stripos($headers['Accept-Encoding'], 'gzip')) || is_int(stripos($headers['Accept-Encoding'], 'deflate'))) {
                $gzip_accept = true;
            }
        }

        if (is_bool(WingedConfig::$config->USE_GZENCODE) && WingedConfig::$config->USE_GZENCODE === true && $gzip_accept) {
            $use_gzencode = true;
        }

        if ($use_gzencode) {
            if (ini_get('zlib.output_compression') == true) {
                ini_set("zlib.output_compression", "Off");
            }
        }

        WingedConfig::$config->set('USE_GZENCODE', $use_gzencode);

        Route::get('__winged_file_handle_core__/{file}', function ($file) {
            header_remove();
            $file = new File(base64_decode($file), false);
            if ($file->exists() && $file->getMimeType()) {
                Buffer::kill();
                $response = new HttpResponseHandler();
                $response->dispatchFile($file);
            }else{
                header('HTTP/1.0 404 Not Found');
            }
            exit;
        });


        if (file_exists(self::$router_dir) && is_directory(self::$router_dir)) {
            if (file_exists(self::$routed_file)) {
                include_once self::$routed_file;
                if (!RouteExec::execute()) {
                    exit;
                }
            }
        }

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
                if ($before === false || $before === null) {
                    RouteExec::sendErrorResponse();
                }
                $file = new File(WingedConfig::$config->NOTFOUND, false);
                if ($file->exists()) {
                    Buffer::reset();
                    include_once WingedConfig::$config->NOTFOUND;
                    Buffer::flushKill();
                    exit;
                } else {
                    Error::_die('END_OF_EXECUTION', 'Nothing exists. Controller no exists, action no exists, routes not found and [Not found] file not exists.', __LINE__, __FILE__, __LINE__);
                }
            } else {
                exit;
            }
        }
    }

    private static function controller_info()
    {
        $exploded_parent = WingedLib::explodePath(self::$parent);
        $controller_info = str_replace(self::$parent, '', self::$uri);
        $controller_info = WingedLib::explodePath($controller_info);
        if (!$exploded_parent && !$controller_info) {
            return ['controller' => self::$standard_controller, 'action' => 'index'];
        } else {
            if (count7($controller_info) == 0) {
                return ['controller' => self::$standard_controller, 'action' => 'index'];
            } else if (count7($controller_info) == 1) {
                return ['controller' => $controller_info[0], 'action' => 'index'];
            } else {
                return ['controller' => $controller_info[0], 'action' => $controller_info[1]];
            }
        }
    }

    /**
     * normalize uri and ignore domain name and all folder before root of application
     */
    public static function normalize()
    {
        $final_uri = '';
        $site_name = '';

        $base_uri = server("request_uri");
        $free_get = explode("?", $base_uri);

        if (count7($free_get) > 1) {
            self::$free_get = $free_get[1];
        } else {
            self::$free_get = '';
        }

        $index_folder = WingedLib::explodePath(WingedLib::clearDocumentRoot());
        $index_folder = end($index_folder);

        $exploded_uri = WingedLib::explodePath($free_get[0]);

        $root_found_in_uri = false;

        if ($index_folder && $exploded_uri) {
            if (in_array($index_folder, $exploded_uri)) {
                $root_found_in_uri = true;
            }
        }

        self::$host = WingedLib::convertslash(server("server_name"));

        if (is_int(stripos(self::$host, 'www.'))) {
            self::$have_www_in_request = true;
        }

        $change_concat = false;

        if ($exploded_uri) {
            foreach ($exploded_uri as $uri) {
                if (!$change_concat && $root_found_in_uri) {
                    $site_name .= '/' . $uri;
                } else {
                    $final_uri .= '/' . $uri;
                }
                if ($uri === $index_folder) {
                    $change_concat = true;
                }
            }
        }

        $site_name = WingedLib::clearPath($site_name);
        $final_uri = WingedLib::normalizePath($final_uri);

        self::$https = "https://" . self::$host . '/' . $site_name . "/";
        self::$http = "http://" . self::$host . '/' . $site_name . "/";

        self::$uri = $final_uri;

        if (count7($free_get) > 1) {
            self::$pure_uri = $final_uri . '?' . $free_get[1];
        } else {
            self::$pure_uri = $final_uri;
        }

        if (server('https')) {
            if (server('https') != 'off') {
                self::$protocol = self::$https;
                self::$have_https_protocol_in_request = true;
            } else {
                self::$protocol = self::$http;
                self::$have_https_protocol_in_request = false;
            }
        } else {
            self::$protocol = self::$http;
            self::$have_https_protocol_in_request = false;
        }
        if (server('http_x_forwarded_proto') != false) {
            if (server('http_x_forwarded_proto') === 'https') {
                self::$have_https_protocol_in_request = true;
                self::$protocol = self::$https;
            }
        }
    }

    /**
     * return page name or controler name, current dir of application and params founded in uri
     * @return array
     */
    private static function getdir()
    {
        $exploded_uri = WingedLib::explodePath(self::$uri);
        $dir = WingedLib::normalizePath();
        $before_concat_dir = WingedLib::normalizePath();
        if ($exploded_uri) {
            foreach ($exploded_uri as $key => $uri_part) {
                $dir .= $uri_part;
                $dir = WingedLib::normalizePath($dir);
                if (is_directory($dir)) {
                    unset($exploded_uri[$key]);
                    $before_concat_dir = $dir;
                }
            }

            self::$parent = $before_concat_dir;

            $before_concat_dir = WingedLib::clearPath($before_concat_dir);

            self::$https_parent = self::$https . $before_concat_dir . '/';
            self::$http_parent = self::$http . $before_concat_dir . '/';
            self::$protocol_parent = self::$protocol . $before_concat_dir . '/';

            if (self::$free_get != '') {
                self::$full_url_provider = self::$protocol . WingedLib::clearPath(self::$pure_uri) . '/?' . self::$free_get;
            } else {
                self::$full_url_provider = self::$protocol . WingedLib::clearPath(self::$pure_uri) . '/';
            }

            if (count7($exploded_uri) == 0) {
                self::$is_standard = true;
                return [
                    "page" => self::$standard,
                    "parent" => $dir,
                    "params" => []
                ];
            } else {
                $exploded_uri = array_values($exploded_uri);
                $page = $exploded_uri[0];
                unset($exploded_uri[0]);
                $params = [];
                foreach ($exploded_uri as $key => $value) {
                    array_push($params, $value);
                }
                return [
                    "page" => $page,
                    "parent" => $dir,
                    "params" => $params
                ];
            }
        } else {
            self::$https_parent = self::$https;
            self::$http_parent = self::$http;
            self::$protocol_parent = self::$protocol;
            if (self::$free_get != '') {
                self::$full_url_provider = self::$protocol . WingedLib::clearPath(self::$pure_uri) . '/?' . self::$free_get;
            } else {
                self::$full_url_provider = self::$protocol . WingedLib::clearPath(self::$pure_uri) . '/';
            }
        }
        self::$is_standard = true;
        return [
            "page" => self::$standard,
            "parent" => "./",
            "params" => []
        ];
    }

    public
    static function return_path_route()
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

    public
    static function post()
    {
        if ($_POST) {
            return $_POST;
        }
        return [];
    }

    public
    static function get()
    {
        if ($_GET) {
            return $_GET;
        }
        return [];
    }

    public
    static function initialJs()
    {
        return '<script>
                    window.protocol = "' . self::$protocol . '"; 
                    window.parent = "' . self::$parent . '"; 
                    window.page_surname = "' . self::$page_surname . '"; 
                    window.uri = "' . self::$uri . '"; 
                    window.controller_params = JSON.parse(\'' . json_encode(self::$controller_params) . '\'); 
                    window.controller_action = "' . self::$controller_action . '";                
                </script>';
    }
}