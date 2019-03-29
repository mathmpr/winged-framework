<?php

namespace Winged;

class WingedConfigDefaults
{
    /**
     * @var null | WingedConfigDefaults
     * no delete this property
     */
    public static $runtime_configs = true;

    /**
     * @property $HTML_CHARSET string
     * set charset for content input, output html and internal encoding
     */
    public $HTML_CHARSET = "UTF-8";


    /**
     * @property $DATABASE_CHARSET string
     * set charset for database names
     */
    public $DATABASE_CHARSET = "utf8";


    /**
     * @property $DEV bool
     * set false when you upload your project to final server
     */
    public $DEV = true;


    /**
     * @property $DBEXT bool
     * on | off mysql extensions and all class. Inflicts DelegateQuery, QueryBuilder, CurrentDB, Connections, Database, DbDict, Models and Migrate class
     */
    public $DBEXT = true;


    /**
     * @property $USE_PREPARED_STMT bool
     * on | off prepared statements
     * view more of prepared statements in
     * <your_domain_name>/winged/what_is_prepared_statement
     */
    public $USE_PREPARED_STMT = NO_USE_PREPARED_STMT;


    /**
     * @property $DB_DRIVER string
     * defines what type of database your project will use.
     * if your server does not support the PDO class.
     * only mysql will be available for use. To see the availability of classes and functions of your server,
     * go to <your_domain_name>/winged/available#database
     */
    public $DB_DRIVER = DB_DRIVER_MYSQL;


    /**
     * @property $STD_DB_CLASS string
     * defines which class will be used for the interaction between PHP and the database
     */
    public $STD_DB_CLASS = IS_MYSQLI;


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
     * @property $HOST string
     * defines default server name for mysql connection
     */
    public $HOST = false;


    /**
     * @property $USER string
     * default user name for mysql connection
     */
    public $USER = false;


    /**
     * @property $DBNAME string
     * default database name for mysql connection
     */
    public $DBNAME = false;


    /**
     * @property $PASSWORD string
     * default password for mysql connection
     */
    public $PASSWORD = false;


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


    private $vars = [];

    public function set($name = '', $value = null)
    {
        $this->vars[$name] = $value;
    }

    public function get($name = null)
    {
        return array_key_exists($name, $this->vars) ? $this->vars[$name] : null;
    }

    public static function init()
    {
        WingedConfigDefaults::$runtime_configs = new WingedConfigDefaults();
        WingedConfig::$config = new WingedConfig();
        if((WingedConfig::$config->HOST === 'localhost' || WingedConfig::$config->HOST === '127.0.0.1') && trim(server('server_addr')) != '::1'){
            WingedConfig::$config->HOST = server('server_addr');
        }
    }

}