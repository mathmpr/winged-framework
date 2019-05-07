<?php

namespace Winged;

use WingedConfig;
use WingedDatabaseConfig;

/**
 * This file is responsible for the general configuration of the framework
 * all the properties in it are defined and exist. You are free to create
 * constants and properties within the WingedConfig class to use in your
 * project globally at a later time. You can also override the properties
 * within other config.php files. These files must be created inside
 * other directories if you want to overwrite some properties at runtime.
 */
class WingedConfigDefaults
{
    /**
     * @property $HTML_CHARSET string
     * set charset for content input, output html and internal encoding
     */
    public $HTML_CHARSET = "UTF-8";

    /**
     * @property $DEV bool
     * set false when you upload your project to final server
     */
    public $DEV = true;

    /**
     * @property $STANDARD string
     * your main and default route for rewrite url
     */
    public $STANDARD = false;

    /**
     * @property $STANDARD_CONTROLLER string
     * defines the name of your primary controller when no name for controllador was found in the url
     */
    public $STANDARD_CONTROLLER = false;

    /**
     * @property $CONTROLLER_DEBUG bool
     * on | off erros and warning of main Controller class
     */
    public $CONTROLLER_DEBUG = true;

    /**
     * @property $PARENT_FOLDER_MVC bool
     * on | off search for better structure MVC folder within folders defined by URL
     * !IMPORTANT: true is recommended, because it enhances the organization of your project
     */
    public $PARENT_FOLDER_MVC = true;

    /**
     * @property $HEAD_CONTENT_PATH string
     * defines path to include in every page called in any Controller by method renderHtml()
     * this option can be rewrited with method rewriteHeadContentPath() of any Controller
     */
    public $HEAD_CONTENT_PATH = null;

    /**
     * @property $ROUTER string
     * defines the behavior for the treatment of url and folder layout of your project
     * PARENT_ROUTES_ROUTE_PHP search parent folder with name "routes" and search file "routes.php" inside this folder
     * PARENT_DIR_PAGE_NAME search parent folder with name "routes" and search file "<page from url>.php" inside this folder
     * ROOT_ROUTES_PAGE_NAME search folder with name "routes" in level of main "index.php" and search file "<page from url>.php" inside this folder
     * ROOT_ROUTES_ROUTE_PHP search folder with name "routes" in level of main "index.php" and search file "routes.php" inside this folder
     */
    public $ROUTER = PARENT_ROUTES_ROUTE_PHP;

    /**
     * @property $FORCE_NOTFOUND bool
     * ignore errors on the controllers and the routes, always forcing the presentation of the page not found
     */
    public $FORCE_NOTFOUND = true;

    /**
     * @var bool
     * if this property is true, the default response for 404 not found got be with a route system
     */
    public $USE_404_WITH_ROUTES = true;

    /**
     * @property $TIMEZONE string
     * sets the time zone used in the entire system
     */
    public $TIMEZONE = "UTC";

    /**
     * @property $NOTFOUND string
     * defines the path to the page file not found
     */
    public $NOTFOUND = false;

    /**
     * @property $DEBUG bool
     * on | off display errors
     */
    public $DEBUG = true;

    /**
     * @property $INTERNAL_ENCODING array
     * this property defines the internal enconding of PHP, it uses [mb] lib
     */
    public $INTERNAL_ENCODING = "UTF-8";

    /**
     * @property $OUTPUT_ENCODING array
     * this property defines the html output enconding, it uses [mb] lib
     */
    public $OUTPUT_ENCODING = "UTF-8";

    /**
     * @var $USE_UNICID_ON_INCLUDE_ASSETS bool | array
     * On some servers, especially those of productions, it is very common some cache system exists
     * for files that are always loaded on the page as files with the extension * .js, * .css, * .svg and etc..
     * Once they finish The entire production site leave this option as false
     * so that your project loads faster and offers a better end-user experience.
     * you can add what the targets will be, adding the name of the tags within the array
     */
    public $USE_UNICID_ON_INCLUDE_ASSETS = false;

    /**
     * @var $AUTO_MINIFY bool | int
     * this property run on assets queue of all Controllers see for diferences after any request
     * see order and paths name and creates a new minified file
     * turn this to false for free all resquests of auto minify ou put an int value in minutes to renew minify
     * if an update can be detected in any file in queue the minified resulted file before change was deleted and other as created
     * on end of project turn this to false
     */
    public $AUTO_MINIFY = false;

    /**
     * @var $FORCE_WWW bool
     * this property force WWW in url with a redirect to same URL of request
     * redirect throw a 301 Moved Permanently to browser
     * if this property is true but www was encontred in request, Winged do not make redirect
     */
    public $FORCE_WWW = false;

    /**
     * @var $FORCE_HTTP bool
     * this property force https protocol in url with a redirect to same URL of request
     * redirect throw a 301 Moved Permanently to browser
     * if this property is true but HTTPS was encontred in request, Winged do not make redirect
     */
    public $FORCE_HTTPS = false;

    /**
     * @var $USE_GZENCODE bool
     * this property tries to enable the output for the responses with deflate or gzip compression.
     * It will only be enabled if the PHP instance supports gzencode or gzdefalte. In addition,
     * the response will only be compressed if the client request allows compression.
     */
    public $USE_GZENCODE = false;

    /**
     * @var bool
     * this property tries to enable cache constrol for static files.
     */
    public $ADD_CACHE_CONTROL = true;

    /**
     * @property $INCLUDES array
     * it includes all paths that are within that variable if they exist and are a valid php file
     * util if you have two classes with same name, and autoload can't load these classes
     */
    public $INCLUDES = [];

    /**
     * @var $vars array
     */
    private $vars = [];

    /**
     * @var $databaseConfig \WingedDatabaseConfig | null
     */
    private $databaseConfig = null;

    /**
     * set an custom property inside vars
     *
     * @param string $name
     * @param null   $value
     */
    public function set($name = '', $value = null)
    {
        $this->vars[$name] = $value;
    }

    /**
     * get an custom property inside vars
     *
     * @param null $name
     *
     * @return bool|mixed
     */
    public function get($name = null)
    {
        return array_key_exists($name, $this->vars) ? $this->vars[$name] : false;
    }

    /**
     * return an access to database config
     *
     * @return \WingedDatabaseConfig|null
     */
    public function db()
    {
        return $this->databaseConfig;
    }

    public function __construct()
    {
        $this->databaseConfig = new WingedDatabaseConfig($this);
    }

    public static function init()
    {
        WingedConfig::$config = new WingedConfig();
        if ((WingedConfig::$config->db()->HOST === 'localhost' || WingedConfig::$config->db()->HOST === '127.0.0.1') && trim(server('server_addr')) != '::1') {
            WingedConfig::$config->db()->HOST = server('server_addr');
        }
    }

}