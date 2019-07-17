<?php

namespace Winged\Frontend;

use Winged\Controller\Controller;
use Winged\Date\Date;
use Winged\External\MatthiasMullie\Minify\Minify\JS;
use Winged\File\File;
use Winged\Utils\RandomName;

/**
 * render view files in project
 *
 * Class MinifyJS
 *
 * @package Winged\Frontend
 */
class MinifyJS extends MinifyMaster
{

    /**
     * MinifyJS constructor.
     *
     * @param null $controller
     */
    public function __construct($controller = null)
    {
        /**
         * @var $controller null | Controller | Assets | Render
         */
        $this->controller = $controller;
        parent::__construct();
    }


    /**
     * create minify information for ./minify.json
     * create cache file with all content inside js files stack
     * remove all js files from stack and append resulted js file inside stack
     * !IMPORTANT: files in stack with URL type not affected by this behavior
     *
     * @param string $path
     * @param string $read
     *
     * @return bool|mixed|File
     */
    public function activeMinify($path, $read)
    {
        $read[$path] = [
            'create_at' => Date::now()->timestamp(),
            'formed_with' => [],
            'cache_file' => './cache/js/' . RandomName::generate('sisisisi', true, false) . '.js'
        ];
        $cache_file = new File($read[$path]['cache_file']);
        $minify = new JS();
        $jsString = '';
        foreach ($this->controller->js as $identifier => $content) {
            if ($content['type'] === 'file') {
                $file = new File($content['string'], false);
                if ($file->exists()) {
                    $jsString .= $file->read();
                    $this->controller->removeJs($identifier);
                    $read[$path]['formed_with'][$content['string']] = [
                        'time' => $file->modifyTime(),
                        'path' => $file->file_path,
                        'name' => $file->file,
                        'identifier' => $identifier,
                    ];
                }
            }
        }
        $cache_file->write($minify->add($jsString)->minify());
        $this->controller->js = array_merge([$path => ['string' => $cache_file->file_path, 'type' => 'file']], $this->controller->js);
        return $this->createMinify($read);
    }


}