<?php

if (!class_exists('WingedConfig')) {

    class WingedConfig
    {
        public static $USE_PREPARED_STMT = null;
        public static $DB_DRIVER = null;
        public static $STD_DB_CLASS = null;
        public static $MAIN_CONTENT_TYPE = 'text/html';
        public static $HTML_CHARSET = 'UTF-8';
        public static $DEV = true;
        public static $DBEXT = false;
        public static $STANDARD = "install";
        public static $STANDARD_CONTROLLER = "install";
        public static $CONTROLLER_DEBUG = true;
        public static $PARENT_FOLDER_MVC = true;
        public static $HEAD_CONTENT_PATH = null;
        public static $HOST = null;
        public static $USER = null;
        public static $DBNAME = null;
        public static $PASSWORD = null;
        public static $ROUTER = PARENT_DIR_PAGE_NAME;
        public static $FORCE_NOTFOUND = true;
        public static $TIMEZONE = null;
        public static $NOTFOUND = null;
        public static $DEBUG = true;
        public static $NOT_WINGED = true;
        public static $INCLUDES = [];
        public static $INTERNAL_ENCODING = 'utf-8';
        public static $OUTPUT_ENCODING = 'utf-8';
        public static $USE_UNICID_ON_INCLUDE_ASSETS = true;
    }
} else {
    WingedConfig::$USE_PREPARED_STMT = null;
    WingedConfig::$DB_DRIVER = null;
    WingedConfig::$STD_DB_CLASS = null;
    WingedConfig::$MAIN_CONTENT_TYPE = 'text/html';
    WingedConfig::$HTML_CHARSET = 'UTF-8';
    WingedConfig::$DEV = true;
    WingedConfig::$DBEXT = false;
    WingedConfig::$STANDARD = "install";
    WingedConfig::$STANDARD_CONTROLLER = "install";
    WingedConfig::$CONTROLLER_DEBUG = true;
    WingedConfig::$PARENT_FOLDER_MVC = true;
    WingedConfig::$HEAD_CONTENT_PATH = null;
    WingedConfig::$HOST = null;
    WingedConfig::$USER = null;
    WingedConfig::$DBNAME = null;
    WingedConfig::$PASSWORD = null;
    WingedConfig::$ROUTER = PARENT_DIR_PAGE_NAME;
    WingedConfig::$FORCE_NOTFOUND = true;
    WingedConfig::$TIMEZONE = null;
    WingedConfig::$NOTFOUND = null;
    WingedConfig::$DEBUG = true;
    WingedConfig::$NOT_WINGED = true;
    WingedConfig::$INCLUDES = [];
    WingedConfig::$INTERNAL_ENCODING = 'utf-8';
    WingedConfig::$OUTPUT_ENCODING = 'utf-8';
    WingedConfig::$USE_UNICID_ON_INCLUDE_ASSETS = true;
}
