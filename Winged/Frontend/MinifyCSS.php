<?php

namespace Winged\Frontend;

use Winged\Controller\Controller;
use Winged\Date\Date;
use Winged\External\MatthiasMullie\Minify\Minify\CSS;
use Winged\File\File;
use Winged\Formater\Formater;
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

    private $currentFileRunner = null;

    private $allCssPaths = [];

    /**
     * MinifyCSS constructor.
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

    public function activeMinify($path, $read)
    {
        $pattern = '/url\((?![\'"]?(?:data|http):)[\'"]?([^\'"\)]*)[\'"]?\)/i';
        $read[$path] = [
            'create_at' => Date::now()->timestamp(),
            'formed_with' => [],
            'cache_file' => './cache/css/' . RandomName::generate('sisisisi', true, false) . '.css'
        ];

        $cache_file = new File($read[$path]['cache_file']);
        $minify = new CSS();

        foreach ($this->controller->css as $identifier => $content) {
            if ($content['type'] === 'file') {
                $file = new File($content['string'], false);
                if ($file->exists()) {
                    $this->currentFileRunner = $file->file_path;
                    $this->allCssPaths[$this->currentFileRunner] = [];
                    $cssContent = $file->read();
                    preg_replace_callback($pattern, function ($matches) use ($file, $pattern, $cssContent) {
                        $this->minifyFileRunner($matches, $file, $pattern, $cssContent);
                    }, $cssContent);
                    $minify->add($file->read());
                    $this->controller->removeCss($identifier);
                    $read[$path]['formed_with'][$content['string']] = [
                        'time' => $file->modifyTime(),
                        'path' => $file->file_path,
                        'name' => $file->file,
                        'identifier' => $identifier,
                    ];
                }
            }
        }

        $cssString = '';

        if (!empty($this->allCssPaths)) {
            $keys = array_keys($this->allCssPaths);
            $cssString = $this->mergeCssFiles(array_shift($keys), $cssString, $keys);
            pre_clear_buffer_die($keys);
        }

        pre_clear_buffer_die($this->allCssPaths);

        $cache_file->write($minify->minify());
        $this->controller->css = array_merge([$path => ['string' => $cache_file->file_path, 'type' => 'file']], $this->controller->css);
        return $this->createMinify($read);
    }

    function mergeCssFiles($fileAsKey, $currentCssString, &$allFilesAsKeys)
    {
        $currentFile = new File($fileAsKey, false);
        if ($currentFile->exists()) {
            $currentCssString .= $currentFile->read();
            foreach ($this->allCssPaths[$fileAsKey] as $information) {
                if ($information['import'] && $information['extension'] === 'css') {
                    $file = new File($information['file_path'], false);
                    if ($file->exists()) {
                        $currentCssString = str_replace($information['full_string'] . ';', $file->read(), $currentCssString);
                        if(!empty($this->allCssPaths[$information['file_path']])){
                            $search = array_search($information['file_path'], $allFilesAsKeys);
                            if(!is_bool($search)){
                                unset($allFilesAsKeys[$search]);
                            }
                            $currentCssString .= $this->mergeCssFiles($information['file_path'], $currentCssString, $allFilesAsKeys);

                        }
                    }
                }
            }
        }
        return $currentCssString;
    }

    function minifyFileRunner($matches, $_file, $pattern, $read)
    {
        /**
         * @var $_file File
         */
        $full_string = $matches[0];
        $file = str_replace(['"', "'"], '', $matches[1]);
        $now_in = $this->currentFileRunner;

        $file = explode(')', $file);
        $file = array_shift($file);

        $explodeBar = explode('/', $file);
        $fileName = array_pop($explodeBar);
        $filePath = join('/', $explodeBar);

        $explodeExtension = explode('.', $fileName);
        $endExtension = end($explodeExtension);
        $endExtension = str_replace(['#', '@', '?', '-', '!', '&', '_', '=', '+'], '~', $endExtension);
        $endExtension = explode('~', $endExtension);
        $cleanedExtension = $endExtension[0];
        $fileName = explode('.' . $cleanedExtension, $fileName);
        $cleanedFileName = array_shift($fileName) . '.' . $cleanedExtension;
        $file = $filePath . '/' . $cleanedFileName;
        $import = false;

        preg_match_all("/@import[ ]*['\"]{0,}(url\()*['\"]*([^;'\"\)]*)['\"\)]*/ui", $read, $matches);
        if (!empty($matches)) {
            if (isset($matches[0][0])) {
                $full_string = $matches[0][0];
                $import = true;
            }
        }

        $filePath = '';

        $this->allCssPaths[$this->currentFileRunner][] = [
            'full_string' => $full_string,
            'file' => $file,
            'extension' => $cleanedExtension,
            'import' => $import,
            'file_path' => &$filePath,
        ];

        if ($cleanedExtension === 'css') {
            $_file = new File($_file->folder->folder . $file, false);
            if ($_file->exists()) {
                $filePath = $_file->file_path;
                $this->currentFileRunner = $_file->file_path;
                $this->allCssPaths[$this->currentFileRunner] = [];
                $cssContent = $_file->read();
                preg_replace_callback($pattern, function ($matches) use ($_file, $pattern, $cssContent) {
                    $this->minifyFileRunner($matches, $_file, $pattern, $cssContent);
                }, $cssContent);
            }
            $this->currentFileRunner = $now_in;
        }
    }

}