<?php

use Winged\WingedConfigDefaults;

/**
 * Customize your application here
 *
 * Class WingedConfig
 */
class WingedConfig extends WingedConfigDefaults
{
    /**
     * @var null | WingedConfig
     * no delete this property
     */
    public static $config = null;
    public $DEFAULT_URI = "home";
    public $TIMEZONE = "America/Sao_Paulo";
    public $NOTFOUND = "./404.php";
    public $AUTO_MINIFY = false;
    public $USE_WINGED_FILE_HANDLER = false;
    public $HTML_LANG = 'pt-BR';
}