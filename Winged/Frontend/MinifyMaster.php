<?php

namespace Winged\Frontend;

use mysql_xdevapi\Exception;
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
class MinifyMaster
{

    /**
     * @var null | Controller | Assets | Render $controller
     */
    public $controller = null;

    /**
     * @var null | File
     */
    public $minify_info = null;

    abstract public function activeMinify($path, $read);

    /**
     * MinifyMaster constructor.
     */
    public function __construct()
    {
        $this->createMinify();
    }

    /**
     * @param bool $content
     */
    protected function createMinify($content = false)
    {
        $minify = new File('./minify.json', false);
        if (!$minify->exists()) {
            $minify = new File('./minify.json');
        }
        if ($content) {
            $minify->write($content);
        }
        $this->minify_info = $minify;
    }

    /**
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

    protected function minify($property = null)
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
                            if (!array_key_exists($former['identifier'], $this->controller{$property})) {
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
                                $this->controller->removeCss($former['identifier']);
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