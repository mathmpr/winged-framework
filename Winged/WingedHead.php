<?php

namespace Winged;

use \Exception;
use Winged\Date\Microtime;
use Winged\Buffer\Buffer;
use Winged\Error\Error;
use Winged\Http\Session;
use Winged\Utils\Container;
use Winged\Database\Connections;
use Winged\Utils\FileTree;
use WingedConfig;

/**
 * Class WingedHead
 *
 * @package Winged
 */
class WingedHead
{
    /**
     * #1 - include major files
     * #2 - init auto load module
     * #3 - start primary buffer
     * #4 - setup internal encoding
     * #5 - include config files
     *
     *
     * @throws Exception
     */
    public static function init()
    {
        global $_ORIGINAL_POST, $_ORIGINAL_GET;

        $_ORIGINAL_POST = $_POST;
        $_ORIGINAL_GET = $_GET;

        global $__autoload__cache;
        $__autoload__cache = false;

        include_once CLASS_PATH . 'Configs/Functions.php';
        include_once CLASS_PATH . 'Utils/FileTree.php';
        include_once CLASS_PATH . 'Buffer/Buffer.php';
        include_once CLASS_PATH . 'Error/ShutdownCallback.php';
        include_once CLASS_PATH . 'Error/SilencedErrors.php';
        include_once CLASS_PATH . 'Error/Error.php';

        if (file_exists(CLASS_PATH . 'Autoload.Cache.php')) {
            include_once CLASS_PATH . 'Autoload.Cache.php';
        }

        date_default_timezone_set('GMT');

        /**
         * @param $className
         */

        global $__autoload__cache__memory;
        $__autoload__cache__memory = false;

        /**
         * @param $className
         */
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
         *
         * @param $className
         */
        spl_autoload_register(function ($className) {
            $className = explode('\\', $className);
            $className = end($className);
            if (class_exists('Winged\Winged')) {
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

        Microtime::init();

        Buffer::start();

        Container::$self = new Container(Container::$self);

        if (!file_exists(PATH_DATABASE_CONFIG)) {
            throw new Exception('file WingedDatabaseConfig.php do not exists.');
        } else {
            include_once PATH_DATABASE_CONFIG;
            if (!class_exists('WingedDatabaseConfig')) {
                throw new Exception('class WingedDatabaseConfig do not exists in WingedDatabaseConfig.php');
            }
        }

        if (!file_exists(PATH_CONFIG)) {
            throw new Exception('file WingedConfig.php do not exists.');
        } else {
            include_once PATH_CONFIG;

            if (!class_exists('WingedConfig')) {
                throw new Exception('class WingedConfig do not exists in WingedConfig.php');
            } else {
                if (!property_exists('WingedConfig', 'config')) {
                    throw new Exception('property self::$config not exists in WingedConfig class');
                } else {
                    WingedConfigDefaults::init();
                }
            }

            /**
             * Auto load for folder MANUAL INCLUDE PATH + ./models/
             *
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

            if (WingedConfig::$config->db()->USE_DATABASE) {
                if (is_bool(stripos(server('request_uri'), '__winged_file_handle_core__'))) {
                    Connections::init();
                    $_GET = no_injection_array($_ORIGINAL_GET);
                    $_POST = no_injection_array($_ORIGINAL_POST);
                }
            }

            if (file_exists(EXTRAS)) {
                include_once EXTRAS;
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