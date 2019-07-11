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
    public $AUTO_MINIFY = 1;
    public $USE_WINGED_FILE_HANDLER = true;
    public $USE_UNICID_ON_INCLUDE_ASSETS = ['link', 'script'];
    public $HTML_LANG = 'pt-BR';
}