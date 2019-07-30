<?php

include_once \Winged\Winged::$parent . 'models/ImageProcessor.php';
include_once \Winged\Winged::$parent . 'models/UploadAbstract.php';
include_once \Winged\Winged::$parent . 'classes/Admin.php';
include_once \Winged\Winged::$parent . 'classes/Paginate.php';

\WingedConfig::$config->DEFAULT_URI = 'login';
\WingedConfig::$config->db()->DBNAME = 'admin';
\WingedConfig::$config->DEFAULT_URI = 'login';
\WingedConfig::$config->INCLUDES = './projects/';
\WingedConfig::$config->USE_WINGED_FILE_HANDLER = false;
\WingedConfig::$config->AUTO_MINIFY = false;
\WingedConfig::$config->HEAD_CONTENT_PATH = './admin/head.content.php';
\WingedConfig::$config->INCLUDES = [
    './models/',
    './autoload/',
];