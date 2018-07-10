<?php

namespace Winged\Translate;

use Winged\File\File;
use Winged\Directory\Directory;

/**
 * Class Translate
 */
class Translate
{

    /**
     * @var bool|mixed
     */
    public static $lang = false;

    /**
     * @var bool|File
     */
    public static $file = false;

    /**
     * Translate constructor.
     * @param string $path
     * @param string $lang
     * @param string $default
     */
    public static function init($path = './languages/', $lang = 'PT-br', $default = 'PT-br')
    {
        $folder = new Directory($path, false);
        if($folder->exists()){
            $file = new File($folder->folder . $lang . '.json', false);
            if($file->exists()){
                self::$lang = json_decode($file->read());
                self::$file = $file;
            }else{
                $file = new File($folder->folder . $default . '.json', false);
                if($file->exists()){
                    self::$lang = json_decode($file->read());
                    self::$file = $file;
                }
            }
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public static function get($key){
        if(self::$lang){
            return array_key_exists($key, self::$lang) ? self::$lang[$key] : false;
        }
        return false;

    }

}