<?php

include_once \Winged\Winged::$parent . 'models/ImageProcessor.php';
include_once \Winged\Winged::$parent . 'models/UploadAbstract.php';
include_once \Winged\Winged::$parent . 'classes/Admin.php';
include_once \Winged\Winged::$parent . 'classes/Paginate.php';

\WingedConfig::$config->DEFAULT_URI = 'login';
\WingedConfig::$config->db()->DBNAME = 'admin';
\WingedConfig::$config->DEFAULT_URI = 'login';
\WingedConfig::$config->INCLUDES = './projects/';
\WingedConfig::$config->HEAD_CONTENT_PATH = './projects/admin/head.content.php';