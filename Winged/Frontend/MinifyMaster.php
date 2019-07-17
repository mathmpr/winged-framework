<?php

namespace Winged\Frontend;

use \Exception;
use Winged\Controller\Controller;
use Winged\Date\Date;
use Winged\File\File;
use \WingedConfig;

/**
 * render view files in project
 *
 * Class MinifyMaster
 *
 * @package Winged\Frontend
 */
abstract class MinifyMaster
{

    /**
     * @var $controller null | Controller | Assets | Render
     */
    public $controller = null;

    /**
     * @var $minify_info null | File
     */
    public $minify_info = null;


    /**
     * MinifyMaster constructor.
     */
    public function __construct()
    {
        $this->createMinify();
    }

    /**
     * @param $path string
     * @param $read array
     *
     * @return mixed
     */
    abstract public function activeMinify($path, $read);

    /**
     * write ./minify.json, if $content as passed for this function, content was converted into an json encoded string and writed into ./minify.json
     *
     * @param $content bool | string
     *
     * @return bool|File
     */
    protected function createMinify($content = false)
    {
        $minify = new File('./minify.json', false);
        if (!$minify->exists()) {
            $minify = new File('./minify.json');
        }
        if ($content) {
            if (is_array($content)) {
                $content = json_encode($content);
            }
            return $minify->write($content);
        }
        $this->minify_info = $minify;
        return false;
    }

    /**
     * read ./minify.json and get info as array
     *
     * @return array
     */
    protected function readMinify()
    {
        $this->createMinify();
        $read = [];
        try {
            $data = $this->minify_info->read();
            if ($data !== '' && $data !== false) {
                $read = json_decode($this->minify_info->read(), true);
            }
        } catch (\Exception $exception) {
            $read = [];
        }
        return $read;
    }

    /**
     * execute minify on an property of this class (css or js property)
     * execute the specific minify method for css and js types
     *
     * @param null $property
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function minify($property = null)
    {
        if (!$property || !is_string($property) || !($property !== 'css' || $property !== 'js')) {
            throw new Exception('minify method accept onde string with "css" or "js" values');
        }
        $read = $this->readMinify();
        if (is_int(WingedConfig::$config->AUTO_MINIFY)) {
            $path = false;
            foreach ($this->controller->{$property} as $identifier => $content) {
                if ($content['type'] === 'file') {
                    $path .= $content['string'];
                }
                $path = md5($path);
            }
            if ($path) {
                if (!array_key_exists($path, $read) && $path !== '') {
                    return $this->activeMinify($path, $read);
                } else {
                    if (array_key_exists($path, $read)) {
                        $check = $read[$path];
                        $renew = false;
                        foreach ($check['formed_with'] as $key => $former) {
                            $file = new File($key, false);
                            if (!$file->exists()) {
                                $renew = true;
                            } else {
                                if ($former['time'] != $file->modifyTime()) {
                                    $renew = true;
                                }
                            }
                            if (!array_key_exists($former['identifier'], $this->controller->{$property})) {
                                $renew = true;
                            }
                        }
                        if ((int)Date::now()->diff((new Date($check['create_at'])), ['i'])->minutes > (int)WingedConfig::$config->AUTO_MINIFY) {
                            $renew = true;
                        }
                        if ($renew) {
                            $old_cache_file = new File($read[$path]['cache_file']);
                            if ($old_cache_file->exists()) {
                                $old_cache_file->delete();
                            }
                            return $this->activeMinify($path, $read);
                        } else {
                            foreach ($check['formed_with'] as $key => $former) {
                                if ($property === 'css') {
                                    $this->controller->removeCss($former['identifier']);
                                } else {
                                    $this->controller->removeJs($former['identifier']);
                                }
                            }
                            $this->controller->{$property} = array_merge([$path => ['string' => $check['cache_file'], 'type' => 'file']], $this->controller->{$property});
                        }
                    }
                }
            }
        }
        return false;
    }

}