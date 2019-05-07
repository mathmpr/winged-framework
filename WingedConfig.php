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
    public $STANDARD = "home";
    public $STANDARD_CONTROLLER = "home";
    public $TIMEZONE = "America/Sao_Paulo";
    public $NOTFOUND = "./404.php";
    public $USE_UNICID_ON_INCLUDE_ASSETS = ['img', 'script', 'link', 'source'];
    public $AUTO_MINIFY = 1;
    public $USE_GZENCODE = true;
    public $ADD_CACHE_CONTROL = true;
}
