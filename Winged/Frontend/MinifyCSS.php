<?php

namespace Winged\Frontend;

use Winged\Controller\Controller;
use Winged\Date\Date;
use Winged\External\MatthiasMullie\Minify\Minify\CSS;
use Winged\File\File;
use Winged\Utils\RandomName;
use \WingedConfig;

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

    /**
     * create minify information for ./minify.json
     * create cache file with all content inside css files stack (affect internal imports rules inside these css files recursive)
     * remove all css files from stack and append resulted css file inside stack
     * !IMPORTANT: files in stack with URL type not affected by this behavior
     *
     * @param string $path
     * @param string $read
     *
     * @return bool|mixed|File
     */
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

        $cssString = '';

        foreach ($this->controller->css as $identifier => $content) {
            if ($content['type'] === 'file') {
                $file = new File($content['string'], false);
                if ($file->exists()) {
                    $this->currentFileRunner = $file->file_path;
                    $this->allCssPaths[$this->currentFileRunner] = [];
                    $cssContent = $file->read();
                    $matchesFound = [];
                    preg_replace_callback($pattern, function ($matches) use ($file, $pattern, $cssContent, $matchesFound) {
                        $matchesFound = $matches;
                        $this->minifyFileRunner($matches, $file, $pattern, $cssContent);
                    }, $cssContent);
                    if (!empty($matchesFound)) {
                        $cssString .= $file->read();
                    }
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

        if (!empty($this->allCssPaths)) {
            foreach ($this->allCssPaths as $file => $files) {
                $find = $this->findImport($file);
                if ($find) {
                    $cssString = str_replace([$find . ' ;', $find . ';', $find], str_replace('  ', ' ', file_get_contents($file)), $cssString);
                } else {
                    $cssString .= str_replace('  ', ' ', file_get_contents($file));
                }
                if (WingedConfig::$config->USE_WINGED_FILE_HANDLER) {
                    preg_match_all($pattern, $cssString, $matches);
                    if (!empty($matches)) {
                        foreach ($matches[0] as $key => $match) {
                            $full_string = $match;
                            $fileImported = str_replace(['"', "'"], '', $matches[1][$key]);

                            $fileImported = explode(')', $fileImported);
                            $fileImported = array_shift($fileImported);

                            $explodeBar = explode('/', $fileImported);
                            $fileImportedName = array_pop($explodeBar);
                            $filePath = join('/', $explodeBar);

                            $explodeExtension = explode('.', $fileImportedName);
                            $endExtension = end($explodeExtension);
                            $endExtension = str_replace(['#', '@', '?', '-', '!', '&', '_', '=', '+'], '~', $endExtension);
                            $endExtension = explode('~', $endExtension);
                            $cleanedExtension = $endExtension[0];
                            $fileImportedName = explode('.' . $cleanedExtension, $fileImportedName);
                            $cleanedFileName = array_shift($fileImportedName) . '.' . $cleanedExtension;

                            $fileImported = $filePath . '/' . $cleanedFileName;
                            if ($cleanedExtension !== 'css') {
                                $explodeBar = explode('/', $file);
                                array_pop($explodeBar);
                                $fileImported = join('/', $explodeBar) . '/' . $fileImported;

                                $fileObject = new File($fileImported, false);
                                if ($fileObject->exists()) {
                                    $cssString = str_replace('  ', ' ', $cssString);
                                    $cssString = str_replace([$full_string . ' ;', $full_string . ';', $full_string], 'url("./__winged_file_handle_core__/' . base64_encode($fileObject->file_path) . '")', $cssString);
                                }
                            }
                        }
                    }
                }
            }

            foreach ($this->allCssPaths as $file => $files) {
                foreach ($files as $file) {
                    $cssString = str_replace([$file['full_string'] . ' ;', $file['full_string'] . ';', $file['full_string']], '', $cssString);
                }
            }
        }


        $cache_file->write($minify->add($cssString)->minify());
        $this->controller->css = array_merge([$path => ['string' => $cache_file->file_path, 'type' => 'file']], $this->controller->css);
        return $this->createMinify($read);
    }

    /**
     * check if $filePath are in any import in other file
     *
     * @param $filePath
     *
     * @return bool|mixed
     */
    function findImport($filePath)
    {
        foreach ($this->allCssPaths as $file => $files) {
            foreach ($files as $info) {
                if ($info['file_path'] === $filePath) {
                    return $info['full_string'];
                }
            }
        }
        return false;
    }

    /**
     *
     *
     * @param $matches
     * @param $_file
     * @param $pattern
     * @param $read
     */
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
                foreach ($matches[0] as $match) {
                    if (is_int(stripos($match, $cleanedFileName))) {
                        $full_string = $match;
                        $import = true;
                    }
                }
            }
        }

        if ($cleanedExtension === 'css') {
            $_file = new File($_file->folder->folder . $file, false);
            if ($_file->exists()) {
                $filePath = $_file->file_path;

                $this->allCssPaths[$this->currentFileRunner][$filePath] = [
                    'full_string' => $full_string,
                    'file' => $file,
                    'extension' => $cleanedExtension,
                    'import' => $import,
                    'file_path' => &$filePath,
                ];

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