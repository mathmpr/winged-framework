<?php

use Winged\WingedDatabaseConfigDefaults;

/**
 * Customize your application database config here
 *
 * Class WingedDatabaseConfig
 */
class WingedDatabaseConfig extends WingedDatabaseConfigDefaults{
    public $USE_DATABASE = true;
    public $DB_DRIVER = DB_DRIVER_SQLITE;
    public $HOST = "";
    public $USER = "";
    public $DBNAME = "./winged.db";
    public $PASSWORD = "";
    public $SCHEMA = '';
}