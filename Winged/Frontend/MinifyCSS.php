<?php

namespace Winged\Frontend;

use function Winged\Controller\__recursiveCheck__;
use Winged\Date\Date;
use Winged\External\MatthiasMullie\Minify\Minify\CSS;
use Winged\File\File;
use Winged\Utils\Container;
use Winged\Utils\RandomName;

/**
 * render view files in project
 *
 * Class MinifyCSS
 *
 * @package Winged\Frontend
 */
class MinifyCSS extends MinifyMaster
{
    /**
     * MinifyCSS constructor.
     *
     * @param null $controller
     */
    public function __construct($controller = null)
    {
        $this->controller = $controller;
        parent::__construct();
    }

    public function activeMinify($path, $read){

        $pattern = '/url\((?![\'"]?(?:data|http):)[\'"]?([^\'"\)]*)[\'"]?\)/i';
        $read[$path] = [
            'create_at' => Date::now()->timestamp(),
            'formed_with' => [],
            'cache_file' => './cache/css/' . RandomName::generate('sisisisi', true, false) . '.css'
        ];
        $cache_file = new File($read[$path]['cache_file']);
        $minify = new CSS();
        Container::$self->__css_paths__ = [];

        function __recursiveCheck__($matches, $_file, $pattern, $minify)
        {
            /**
             * @var $_file  File
             * @var $minify CSS
             */
            $full_string = $matches[0];
            $file = str_replace(['"', "'"], '', $matches[1]);
            //$extension = trim($matches[2]);
            $now_in = Container::$self->__css_path_now__;

            $file = explode(')', $file);
            $file = array_shift($file);
            $exp = explode('.', $file);
            $extension = end($exp);

            Container::$self->vars['__css_paths__'][Container::$self->__css_path_now__][] =
                [
                    'full_string' => $full_string,
                    'file' => $file,
                    'extension' => $extension
                ];
            if ($extension == 'css') {
                $_file = new File($_file->folder->folder . $file, false);
                if ($_file->exists()) {
                    Container::$self->vars['__css_path_now__'] = $_file->file_path;
                    Container::$self->vars['__css_paths__'][Container::$self->__css_path_now__] = [];
                    preg_replace_callback($pattern, function ($matches) use ($file, $pattern, $minify) {
                        __recursiveCheck__($matches, $file, $pattern, $minify);
                    }, $_file->read());
                    $minify->add($_file->read());
                }
                Container::$self->vars['__css_path_now__'] = $now_in;
            }
        }

        foreach ($this->css as $identifier => $content) {
            if (!in_array($identifier, $this->remove_css)) {
                if ($content['type'] === 'file') {
                    $file = new File($content['string'], false);
                    if ($file->exists()) {
                        Container::$self->vars['__css_path_now__'] = $file->file_path;
                        Container::$self->vars['__css_paths__'][Container::$self->__css_path_now__] = [];
                        preg_replace_callback($pattern, function ($matches) use ($file, $pattern, $minify) {
                            __recursiveCheck__($matches, $file, $pattern, $minify);
                        }, $file->read());

                        $minify->add($file->read());
                        $this->removeCss($identifier);
                        $read[$path]['formed_with'][$content['string']] = [
                            'time' => $file->modifyTime(),
                            'path' => $file->file_path,
                            'name' => $file->file,
                            'identifier' => $identifier,
                        ];
                    }
                }
            }
        }

        $cache_file->write($minify->minify());
        $this->css = array_merge([$path => ['string' => $cache_file->file_path, 'type' => 'file']], $this->css);
        return $this->persistsMinifiedCacheFileInformation($read);

    }


}