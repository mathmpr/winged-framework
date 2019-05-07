<?php
use Winged\Winged;
define("DOCUMENT_ROOT", str_replace("\\", "/", dirname(__FILE__) . "/"));
include_once "./Winged/Configs/Defines.php";
include_once "./Winged/Configs/IniSets.php";
include_once "./Winged/WingedHead.php";
include_once "./Winged/Winged.php";
Winged::start();