<?php

namespace Winged;

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '120');
ini_set('upload_max_filesize', '64MB');
ini_set('max_file_uploads', '100');
ini_set('post_max_size', '64MB');

define("PARENT_DIR_PAGE_NAME", 1);
define("ROOT_ROUTES_PAGE_NAME", 2);
define("PARENT_ROUTES_ROUTE_PHP", 3);
define("ROOT_ROUTES_ROUTE_PHP", 4);

define("USE_PREPARED_STMT", true);
define("NO_USE_PREPARED_STMT", false);
define("IS_PDO", "PDO");
define("IS_MYSQLI", "MYSQLI");

define("DB_DRIVER_CUBRID", "cubrid:host=%s;port=%s;dbname=%s"); //host, port, dbname, user pass
define("DB_DRIVER_FIREBIRD", "firebird:dbname=%s/%s:%s"); //host(dmname), port(dbname), file_path, user, pass
define("DB_DRIVER_MYSQL", "mysql:host=%s;port=%s;dbname=%s"); //host || unix_socket, port, dbname, user, pass
define("DB_DRIVER_MYSQL_UNIX", "mysql_unix:unix_socket=%s;port=%s;dbname=%s"); //host || unix_socket, port, dbname, user, pass
define("DB_DRIVER_SQLSRV", "sqlsrv:Server=%s,%s;Database=%s"); //host, port, dbname, user, pass
define("DB_DRIVER_PGSQL", "pgsql:dbname=%s;host=%s"); //dbname, host, user, pass
define("DB_DRIVER_SQLITE", "sqlite:%s"); //dbname

define("PATH_CONFIG", "./config.php");
define("PATH_EXTRA_CONFIG", "./extra.config.php");
define("CLASS_PATH", "./Winged/");
define("STD_CONFIG", "./Winged/config/config.php");
define("STD_ROUTES", "./Winged/routes/");

ini_set("display_errors", true);

ini_set("display_startup_errors", true);

umask(0);

clearstatcache();

global $_ORIGINAL_POST, $_ORIGINAL_GET;

$_ORIGINAL_POST = $_POST;
$_ORIGINAL_GET = $_GET;

global $__autoload__cache;
$__autoload__cache = false;

include_once CLASS_PATH . 'Utils/Functions.php';
include_once CLASS_PATH . 'Utils/FileTree.php';
include_once CLASS_PATH . 'Autoload.Cache.php';

use Winged\Utils\FileTree;

/**
 * @param $className
 */
function findClass($className)
{
    $tree = new FileTree();
    $tree->gemTree(CLASS_PATH, ['php']);
    $files = $tree->getFiles();
    $cache = "<?php global " . '$__autoload__cache' . "; ";
    $exec = '$__autoload__cache' . " = [";
    foreach ($files as $file) {
        $exec .= '
    "' . $file . '",';
        if (is_int(strpos($file, $className))) {
            include_once $file;
        }
    }
    $exec = substr($exec, 0, strlen($exec) - 1);
    $exec .= '
];';
    eval($exec);
    file_put_contents(CLASS_PATH . 'autoload.cache.php', $cache . $exec);
}

/**
 * @param $className
 */
function findCache($className){
    global $__autoload__cache;
    $included = false;
    if (is_array($__autoload__cache)) {
        foreach ($__autoload__cache as $file) {
            if (is_int(strpos($file, $className))) {
                if(file_exists($file)){
                    $included = true;
                    include_once $file;
                }
            }
        }
        if (!$included) {
            findClass($className);
        }
    } else {
        findClass($className);
    }
}

spl_autoload_register(function ($className) {
    $className = explode('\\', $className);
    $className = end($className);
    findCache($className);
});

/**
 * Auto load for folder ./models/
 * @param $className
 */
spl_autoload_register(function ($className) {
    $className = explode('\\', $className);
    $className = end($className);
    if(file_exists("./models/" . $className . ".php")){
        include_once "./models/" . $className . ".php";
    }
});

use Winged\Date\Microtime;
use Winged\Buffer\Buffer;
use Winged\Error\Error;
use Winged\Utils\Container;
use Winged\Database\Connections;
use Winged\Controller\Controller;
use Winged\Restful\Restful;
use Winged\Rewrite\Rewrite;
use Winged\Utils\WingedLib;

Microtime::init();

Buffer::start();



Container::$self = new Container(Container::$self);



if (!file_exists(PATH_CONFIG)) {
    Error::_die('file config.php do not exists.', 110, 'winged.class.php', 110);
} else {
    include_once PATH_CONFIG;

    if (!class_exists('Winged\WingedConfig')) {
        Error::_die('class WingedConfig do not exists in config.php', 101, 'winged.class.php', 101);
    }

    if (WingedConfig::$DBEXT) {
        Connections::init();
        $_GET = no_injection_array($_ORIGINAL_GET);
        $_POST = no_injection_array($_ORIGINAL_POST);
    }

    if (file_exists(PATH_EXTRA_CONFIG)) {
        include_once PATH_EXTRA_CONFIG;
    }

    if (!is_null(WingedConfig::$TIMEZONE)) {
        date_default_timezone_set(WingedConfig::$TIMEZONE);
    } else {
        date_default_timezone_set("Brazil/West");
    }

    if (!is_null(WingedConfig::$INCLUDES)) {
        if (gettype(WingedConfig::$INCLUDES) == "array") {
            for ($x = 0; $x < count7(WingedConfig::$INCLUDES); $x++) {
                include_once WingedConfig::$INCLUDES[$x];
            }
        } else {
            include_once WingedConfig::$INCLUDES;
        }
    }
}


if (WingedConfig::$DEBUG) {
    error_reporting(E_ALL);
} else {
    error_reporting(E_WARNING | E_NOTICE | E_ERROR);
}

mb_internal_encoding(WingedConfig::$INTERNAL_ENCODING);
mb_http_output(WingedConfig::$OUTPUT_ENCODING);
header('Content-type: ' . WingedConfig::$MAIN_CONTENT_TYPE . '; charset=' . WingedConfig::$HTML_CHARSET . '');

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
     * @var $rewrite_obj Rewrite
     */
    public static $rewrite_obj = false;
    /**
     * @var $restful_obj Restful
     */
    public static $restful_obj;
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
        if (!self::$rewrite_obj) {
            self::$rewrite_obj = new Rewrite();
            self::$restful_obj = new Restful();
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
        self::restful();

        $arr_ext = ['.php', '.html', '.htm', '.xml', '.json'];

        if (WingedConfig::$NOT_WINGED) {
            $dirs = self::getdir(WingedLib::dotslash(self::$uri), 'pure-html');
            self::$parent = $dirs['parent'];
            self::$page_surname = $dirs['page'];
            foreach ($arr_ext as $ext) {
                if (file_exists(self::$parent . self::$page_surname . $ext)) {
                    include_once self::$parent . self::$page_surname . $ext;
                    if (WingedConfig::$DEBUG && CoreError::warnings()) {
                        CoreBuffer::flush();
                    }
                    exit;
                }
            }
        }

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

        if (!self::$restful) {
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
                        self::$rewrite_obj->rewrite_page();
                    }
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

    public static function restful()
    {
        $uri = WingedLib::dotslash(self::$uri);
        $exp = WingedLib::slashexplode($uri);
        if (count7($exp) > 0 && $exp[0] == "restful") {
            self::$restful = true;
            unset($exp[0]);
            $uri = WingedLib::dotslash(join("/", $exp), true);
            self::$uri = $uri;
        }
    }

    public static function getdir($uri, $extra_dir = false)
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
        return array(
            "page" => self::$standard,
            "parent" => "./",
            "params" => false
        );
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

    /**
     * Example:
     * <code>
     * Winged::addRoute('./init/', array(
     *      "index" => "real_path_to_my_view.php"
     * ));
     *
     * or...
     *
     * Winged::addRoute('./init/pattern_rule_for_this_parameter', array(
     *      "index" => "real_path_to_my_view.php"
     *       new Parameter("my_parameter", "./other_file_include.php"),
     * ));
     * </code>
     * @access public
     * @param string $index path or math to search in url.
     * @param array $route is an array with all parameters.
     * @return void
     */
    public static function addroute($index, $route)
    {
        self::$rewrite_obj->addroute($index, $route);
    }

    public static function addrest($index, $rest)
    {
        self::$restful_obj->addrest($index, $rest);
    }

    public function setDefault404()
    {
        self::$restful_obj->setDefault404();
    }

    public static function post()
    {
        return $_POST;
    }

    public static function get()
    {
        return $_GET;
    }

    public static function initialJs()
    {
        return '<script>
                    window.protocol = "' . Winged::$protocol . '"; 
                    window.page_surname = "' . Winged::$page_surname . '"; 
                    window.uri = "' . Winged::$uri . '"; 
                    window.controller_params = JSON.parse(\'' . json_encode(Winged::$controller_params) . '\'); 
                    window.controller_action = "' . Winged::$controller_action . '";                
                </script>';
    }

}

if (file_exists('./winged.globals.php')) {
    include_once './winged.globals.php';
}