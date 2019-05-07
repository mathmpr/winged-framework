<?php

namespace Winged;

/**
 * This file is responsible for the general configuration of the framework
 * all the properties in it are defined and exist. You are free to create
 * constants and properties within the WingedConfig class to use in your
 * project globally at a later time. You can also override the properties
 * within other config.php files. These files must be created inside
 * other directories if you want to overwrite some properties at runtime.
 */
class WingedConfig extends WingedConfigDefaults
{
    /**
     * @var null | WingedConfig
     * no delete this property
     */
    public static $config = null;
    /**
     * @property $MAIN_CONTENT_TYPE string
     * set content type in header
     */
    public $MAIN_CONTENT_TYPE = "text/html";

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
    public $STANDARD = "home";

    /**
     * @property $STANDARD_CONTROLLER string
     * defines the name of your primary controller when no name for controllador was found in the url
     */
    public $STANDARD_CONTROLLER = "home";

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
     * @property $USE_DATABASE bool
     * on | off mysql extensions and all class. Inflicts DelegateQuery, QueryBuilder, CurrentDB, Connections, Database, DbDict and Models classes
     */
    public $USE_DATABASE = false;

    /**
     * @property $HOST string
     * defines default server name for mysql connection
     */
    public $HOST = "localhost";

    /**
     * @property $USER string
     * default user name for mysql connection
     */
    public $USER = "root";

    /**
     * @property $DBNAME string
     * default database name for mysql connection
     */
    public $DBNAME = "mysql";

    /**
     * @property $PASSWORD string
     * default password for mysql connection
     */
    public $PASSWORD = "";

    /**
     * @property $ROUTER string
     * defines the behavior for the treatment of url and folder layout of your project
     * constant PARENT_ROUTES_ROUTE_PHP search parent folder with name "routes" and search file "routes.php" inside this folder
     * constant PARENT_DIR_PAGE_NAME search parent folder with name "routes" and search file "<page from url>.php" inside this folder
     * constant ROOT_ROUTES_PAGE_NAME search folder with name "routes" in level of main "index.php" and search file "<page from url>.php" inside this folder
     * constant ROOT_ROUTES_ROUTE_PHP search folder with name "routes" in level of main "index.php" and search file "routes.php" inside this folder
     */
    public $ROUTER = PARENT_ROUTES_ROUTE_PHP;

    /**
     * @property $FORCE_NOTFOUND bool
     * ignore errors on the controllers and the routes, always forcing the presentation of the page not found
     */
    public $FORCE_NOTFOUND = true;

    /**
     * @property $TIMEZONE string
     * sets the time zone used in the entire system
     */
    public $TIMEZONE = "America/Sao_Paulo";

    /**
     * @property $NOTFOUND string
     * defines the path to the page file not found
     */
    public $NOTFOUND = "./404.php";

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
    public $USE_UNICID_ON_INCLUDE_ASSETS = ['img', 'script', 'link', 'source'];


    /**
     * @var $AUTO_MINIFY bool | int
     * this var run on assets queue of all Controllers see for diferences after any request
     * see order and paths name and creates a new minified file
     * turn this to false for free all resquests of auto minify ou put an int value in minutes to renew minify
     * if an update can be detected in any file in queue the minified resulted file before change was deleted and other as created
     * on end of project turn this to false
     */
    public $AUTO_MINIFY = 1;

    /**
     * gzencode offers an suporte to html, json, js and css files
     * it reduces size of requests
     */
    public $USE_GZENCODE = true;

    /**
     * @var $FORCE_WWW bool
     * if www not found in URL, Winged make an redirect with same URL with www
     */
    public $FORCE_WWW = false;

    /**
     * @var $FORCE_HTTPS bool
     * if https not found in URL, Winged make an redirect with same URL with https
     */
    public $FORCE_HTTPS = false;

    /**
     * @property $INCLUDES array
     * it includes all paths that are within that variable if they exist and are a valid php file
     * util if you have two classes with same name, and autoload can't load these classes
     */
    public $INCLUDES = [

    ];
}
