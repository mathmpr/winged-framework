<?php

namespace Winged;

use Winged\Date\Microtime;
use Winged\Buffer\Buffer;
use Winged\Error\Error;
use Winged\Http\Session;
use Winged\Utils\Container;
use Winged\Database\Connections;
use Winged\Utils\FileTree;

/**
 * Class WingedHead
 * @package Winged
 */
class WingedHead
{
    public static function init()
    {
        if (file_exists('./winged.globals.php')) {
            include_once './winged.globals.php';
        }

        $persists = 0;

        if(!defined('DOCUMENT_ROOT')){
            $document_root = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
            $document_root = explode('/', $document_root);
            array_pop($document_root);
            $document_root = join('/', $document_root);
            while (!file_exists($document_root . '/Winged')){
                $document_root = explode('/', $document_root);
                array_pop($document_root);
                if(count($document_root) <= 1){
                    $persists++;
                }
                $document_root = join('/', $document_root);
                if($persists === 2){
                    echo 'Die. Folder Winged not found in any location.';
                    exit;
                }
            }
            define('DOCUMENT_ROOT', $document_root . '/');
        }

        if(defined('PARENT_DIR_PAGE_NAME')){
            return null;
        }

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
        define("DB_DRIVER_MYSQL", "mysql:host=%s;port=%s;dbname=%s"); //host, port, dbname, user, pass
        define("DB_DRIVER_SQLSRV", "sqlsrv:Server=%s,%s;Database=%s"); //host, port, dbname, user, pass
        define("DB_DRIVER_PGSQL", "pgsql:host=%s;port=%s;dbname=%s;"); //host, port, dbname, user, pass
        define("DB_DRIVER_SQLITE", "sqlite:%s"); //dbname

        define("PATH_CONFIG", DOCUMENT_ROOT . "config.php");
        define("PATH_EXTRA_CONFIG", DOCUMENT_ROOT . "extra.config.php");
        define("CLASS_PATH", DOCUMENT_ROOT . "Winged/");
        define("STD_CONFIG", DOCUMENT_ROOT . "Winged/config/config.php");
        define("STD_ROUTES", DOCUMENT_ROOT . "Winged/routes/");

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

        if (file_exists(CLASS_PATH . 'Autoload.Cache.php')) {
            include_once CLASS_PATH . 'Autoload.Cache.php';
        }

        date_default_timezone_set('GMT');

        /**
         * @param $className
         */

        global $__autoload__cache__memory;
        $__autoload__cache__memory = false;

        function findClass($className)
        {
            global $__autoload__cache__memory;
            if (!$__autoload__cache__memory) {
                $tree = new FileTree();
                $tree->gemTree(CLASS_PATH, ['php']);
                $files = $tree->getFiles();
                $__autoload__cache__memory = $files;
            } else {
                $files = $__autoload__cache__memory;
            }
            $cache = "<?php global " . '$__autoload__cache' . "; ";
            $exec = '$__autoload__cache' . " = [";
            foreach ($files as $file) {
                $exploded = explode('/', $file);
                $file_name = str_replace('.php', '', end($exploded));

                $exec .= '
    "./' . str_replace(DOCUMENT_ROOT, '', $file) . '",';
                if ($className === $file_name) {
                    include_once $file;
                }
            }
            $exec = substr($exec, 0, strlen($exec) - 1);
            $exec .= '
];';
            eval($exec);
            file_put_contents(CLASS_PATH . 'Autoload.Cache.php', $cache . $exec);
        }

        /**
         * @param $className
         */
        function findCache($className)
        {
            global $__autoload__cache;
            $included = false;
            if (is_array($__autoload__cache)) {
                foreach ($__autoload__cache as $file) {
                    $exploded = explode('/', $file);
                    $file_name = str_replace('.php', '', end($exploded));
                    if ($file_name === $className) {
                        if (file_exists(DOCUMENT_ROOT . $file)) {
                            $included = true;
                            include_once DOCUMENT_ROOT . $file;
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

        /**
         * Auto load for folder DYNAMIC_PARENT_PATH + ./models/
         * Auto load for folder ROOT + ./models/
         * Auto load for folder DYNAMIC_PARENT_PATH + ./autoload/
         * Auto load for folder ROOT + ./autoload/
         * @param $className
         */
        spl_autoload_register(function ($className) {
            $className = explode('\\', $className);
            $className = end($className);
            if(class_exists('Winged\Winged')){
                if (file_exists("./models/" . $className . ".php")) {
                    include_once "./models/" . $className . ".php";
                    return true;
                }
                if (file_exists(Winged::$parent . "models/" . $className . ".php")) {
                    include_once Winged::$parent . "models/" . $className . ".php";
                    return true;
                }
                if (file_exists("./autoload/" . $className . ".php")) {
                    include_once "./autoload/" . $className . ".php";
                    return true;
                }
                if (file_exists(Winged::$parent . "autoload/" . $className . ".php")) {
                    include_once Winged::$parent . "autoload/" . $className . ".php";
                    return true;
                }
            }
            findCache($className);
            return true;
        });

        /**
         * Auto load for folder MANUAL INCLUDE PATH + ./models/
         * @param $className
         */
        spl_autoload_register(function ($className) {
            if (!is_null(WingedConfig::$config->INCLUDES)) {
                if (gettype(WingedConfig::$config->INCLUDES) == "array") {
                    for ($x = 0; $x < count7(WingedConfig::$config->INCLUDES); $x++) {
                        if (file_exists(WingedConfig::$config->INCLUDES[$x] . "models/" . $className . ".php")) {
                            include_once WingedConfig::$config->INCLUDES[$x] . "models/" . $className . ".php";
                        }
                    }
                } else {
                    if (file_exists(WingedConfig::$config->INCLUDES . "models/" . $className . ".php")) {
                        include_once WingedConfig::$config->INCLUDES . "models/" . $className . ".php";
                    }
                }
            }
        });

        Microtime::init();

        Buffer::start();

        Container::$self = new Container(Container::$self);

        if (!file_exists(PATH_CONFIG)) {
            Error::_die('file config.php do not exists.', 110, 'winged.class.php', 110);
        } else {
            include_once PATH_CONFIG;

            if (!class_exists('Winged\WingedConfig')) {
                Error::_die('class WingedConfig do not exists in config.php', 101, 'winged.class.php', 101);
            } else {
                if (!property_exists('Winged\WingedConfig', 'config')) {
                    Error::_die('Die | Fatal', 'property self::$config not exists in WingedConfig class', __LINE__, __FILE__, __LINE__);
                } else {
                    WingedConfigDefaults::init();
                }
            }


            if (Session::get('__WINGED_METHOD_REDIRECT__')) {
                $_SERVER['REQUEST_METHOD'] = Session::get('__WINGED_METHOD_REDIRECT__');
                Session::remove('__WINGED_METHOD_REDIRECT__');
            }

            if (Session::get('__WINGED_POST_REDIRECT__')) {
                $_POST = Session::get('__WINGED_POST_REDIRECT__');
                Session::remove('__WINGED_POST_REDIRECT__');
            }

            if (Session::get('__WINGED_HEADERS_REDIRECT__')) {
                $headers = Session::get('__WINGED_HEADERS_REDIRECT__');
                if (is_array($headers)) {
                    foreach ($headers as $key => $header) {
                        if (!array_key_exists($key, $_SERVER)) {
                            $_SERVER[$key] = $header;
                        }
                    }
                }
            }

            if (WingedConfig::$config->DBEXT) {
                Connections::init();
                $_GET = no_injection_array($_ORIGINAL_GET);
                $_POST = no_injection_array($_ORIGINAL_POST);
            }

            if (file_exists(PATH_EXTRA_CONFIG)) {
                include_once PATH_EXTRA_CONFIG;
            }

            if (!is_null(WingedConfig::$config->TIMEZONE)) {
                date_default_timezone_set(WingedConfig::$config->TIMEZONE);
            } else {
                date_default_timezone_set("UTC");
            }
        }


        if (WingedConfig::$config->DEBUG) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_WARNING | E_NOTICE | E_ERROR);
        }

        mb_internal_encoding(WingedConfig::$config->INTERNAL_ENCODING);
        mb_http_output(WingedConfig::$config->OUTPUT_ENCODING);
    }
}