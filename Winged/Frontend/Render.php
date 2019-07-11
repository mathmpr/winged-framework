<?php

namespace Winged\Frontend;

use Winged\File\File;
use Winged\Buffer\Buffer;
use Winged\Utils\RandomName;
use Winged\Utils\WingedLib;
use Winged\Winged;
use \WingedConfig;

/**
 * render view files in project
 *
 * Class Render
 *
 * @package Winged\Frontend
 */
class Render extends Assets
{

    /**
     * @var bool $first_render
     */
    protected $first_render = true;

    /**
     * @var string $baseUrl
     */
    protected $baseUrl = "./";

    /**
     * @var null | string $currentTag
     */
    protected $currentTag = null;

    /**
     * @var array $unicidAssets
     */
    protected $unicidAssets = [];

    /**
     * @var int $calls
     */
    public $calls = 0;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * abstraction for render
     *
     * @param       $path
     * @param array $vars
     *
     * @return bool|string
     */
    public function _render($path, $vars = [])
    {
        if (file_exists($path) && !is_directory($path)) {
            $this->calls++;
            return $this->_include($path, $vars);
        } else {
            trigger_error("File {$path} can't rendred because file not found.", E_USER_WARNING);
        }
        return false;
    }

    /**
     * check if all render call are maked
     *
     * @return bool
     */
    public function checkCalls()
    {
        return $this->calls === 0;
    }

    /**
     * abstraction for include file and local vars
     *
     * @param       $path
     * @param array $vars
     *
     * @return bool|string
     */
    private function _include($path, $vars = [])
    {
        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                if (!is_int($key) && is_string($key)) {
                    ${$key} = $value;
                }
            }
        }
        if ($this->first_render) {
            $this->first_render = false;
            Buffer::reset();
            $file = new File($path, false);
            $read = $file->read();
            $_pos = stripos($read, '?>');
            if (is_int($_pos)) {
                if ($_pos > 0) {
                    $read = "\n?>\n" . trim($read);
                }
            } else {
                $read = "\n?>\n" . trim($read);
            }
            if ($read[strlen($read) - 1] != ';') {
                $read = $read . "\n<?php\n";
            }
            eval($read);
            $this->calls--;
            $content = Buffer::getKill();
            return $content;
        } else {
            $file = new File($path, false);
            $read = $file->read();
            $_pos = stripos($read, '?>');
            if (is_int($_pos)) {
                if ($_pos > 0) {
                    $read = "\n?>\n" . trim($read);
                }
            } else {
                $read = "\n?>\n" . trim($read);
            }
            if ($read[strlen($read) - 1] != ';') {
                $read = $read . "\n<?php\n";
            }
            eval($read);
            $this->calls--;
        }
        return false;
    }

    /**
     * run inside CSS files and replace path with corret path
     * replace src, href with file handle core if it is true in config
     *
     * @param $content
     */
    protected function reconfigurePaths(&$content)
    {
        preg_replace_callback('#<base.+?href="([^"]*)".*?/?>#i', function ($found) {
            $this->baseUrl = $found[1];
            if ($this->baseUrl == "") {
                $this->baseUrl = "./";
            }
        }, $content);

        $patterns = [
            'script' => '#<script.+?src="([^"]*)".*?/?>#i',
            'img' => '#<img.+?src="([^"]*)".*?/?>#i',
            'source' => '#<source.+?src="([^"]*)".*?/?>#i',
            'link' => '#<link.+?href="([^"]*)".*?/?>#i',
        ];

        if (is_array(WingedConfig::$config->USE_UNICID_ON_INCLUDE_ASSETS)) {
            $this->unicidAssets = WingedConfig::$config->USE_UNICID_ON_INCLUDE_ASSETS;
        }

        if (!empty($this->unicidAssets) || WingedConfig::$config->ADD_CACHE_CONTROL) {
            foreach ($patterns as $tag => $pattern) {
                $this->currentTag = $tag;
                $content = preg_replace_callback($pattern, function ($matches) {
                    return $this->tradePaths($matches);
                }, $content);
            }
        }
    }

    /**
     * modify path to files to file handler core
     *
     * @param $matches
     *
     * @return mixed
     */
    private function tradePaths($matches)
    {
        $full_string = $matches[0];
        $only_match = $matches[1];
        $base = false;

        if (is_string($this->baseUrl)) {
            $base = str_replace(
                [
                    Winged::$protocol,
                    Winged::$http,
                    Winged::$https,
                ],
                '',
                $this->baseUrl
            );
        }

        if (WingedConfig::$config->USE_WINGED_FILE_HANDLER) {
            $copy_match = WingedLib::clearPath($only_match);
            $copy_match = str_replace(
                [
                    Winged::$protocol,
                    Winged::$http,
                    Winged::$https,
                ],
                '',
                $copy_match
            );

            if (is_string($base)) {
                $cantUseBase = false;
                $file = new File(WingedLib::normalizePath($base) . $copy_match, false);
                if (!$file->exists()) {
                    $file = new File($copy_match, false);
                    if ($file->exists()) {
                        $cantUseBase = true;
                    }
                }
                $mime = explode('/', $file->getMimeType());
                $mime = $mime[0];
                if (($file->exists() && in_array($file->getExtension(), [
                            'json',
                            'html',
                            'xml',
                            'css',
                            'htm',
                            'js',
                            'svg'
                        ])) || (($file->exists() && $mime == 'image'))) {
                    if (
                        (!is_int(stripos($copy_match, 'https://')) &&
                            !is_int(stripos($copy_match, 'http://')) &&
                            !is_int(stripos($copy_match, '//'))) ||
                        (is_int(stripos($copy_match, Winged::$http)) ||
                            is_int(stripos($copy_match, Winged::$https))
                        )
                    ) {
                        if ($cantUseBase) {
                            $copy_match = Winged::$protocol . '__winged_file_handle_core__/' . base64_encode($copy_match);
                        } else {
                            $copy_match = Winged::$protocol . '__winged_file_handle_core__/' . base64_encode(WingedLib::normalizePath($base) . $copy_match);
                        }
                        $full_string = str_replace($only_match, $copy_match, $full_string);
                        $only_match = $copy_match;
                    }
                }
            }
        }


        if (in_array($this->currentTag, $this->unicidAssets)) {
            if (is_int(stripos($only_match, '?'))) {
                $full_string = str_replace($only_match, $only_match . '&get=' . RandomName::generate('sisisi'), $full_string);
            } else {
                $full_string = str_replace($only_match, $only_match . '?get=' . RandomName::generate('sisisi'), $full_string);

            }
        }

        return $full_string;
    }

    /**
     * add html, head and body tags
     * and finally add asset files
     *
     * @param $content
     */
    protected function configureAssets(&$content)
    {
        ob_start('mb_output_handler');
        ?>
        <!DOCTYPE html>
        <html <?= $this->htmlId() ? 'id="' . $this->htmlId() . '"' : '' ?>
                lang="<?= WingedConfig::$config->HTML_LANG ?>"
                class="<?php
                foreach ($this->htmlTagClasses as $class) {
                    echo $class;
                }
                ?>">
        <head>
            <?php
            foreach ($this->appendedAbstractHeadContent as $head) {
                $file = new File($head, false);
                if ($file->exists()) {
                    if (in_array($file->getExtension(), ['php'])) {
                        include_once $file->file_path;
                    } else {
                        echo $file->read();
                    }
                } else {
                    echo $head;
                }
            }

            foreach ($this->css as $identifier => $file) {
                if ($file['type'] === 'file') {
                    ?>
                    <link href="<?= Winged::$protocol . $file['string'] ?>" type="text/css" rel="stylesheet"/>
                    <?php
                } else if ($file['type'] === 'script') {
                    ?>
                    <?= $file['string'] ?>
                    <?php
                } else if ($file['type'] === 'url') {
                    ?>
                    <link href="<?= $file['string'] ?>" type="text/css" rel="stylesheet"/>
                    <?php
                }

            }

            ?>
        </head>
        <body <?= $this->bodyId() ? 'id="' . $this->bodyId() . '"' : '' ?>
                class="<?php
                foreach ($this->bodyTagClasses as $class) {
                    echo $class;
                }
                ?>">
        <?php
        echo $content;
        foreach ($this->js as $identifier => $file) {
            if ($file['type'] === 'file') {
                ?>
                <script src="<?= Winged::$protocol . $file['string'] ?>" type="text/javascript"></script>
            <?php
            } else if ($file['type'] === 'script') {
                ?>
                <?= $file['string'] ?>
                <?php
            } else if ($file['type'] === 'url') {
            ?>
                <script src="<?= $file['string'] ?>" type="text/javascript"></script>
                <?php
            }
        }
        ?>
        </body>
        </html>
        <?php
        $content = ob_get_clean();
    }

    /**
     * remove new lines from html
     *
     * @param $content
     */
    protected function compactHtml(&$content)
    {
        if (WingedConfig::$config->COMPACT_HTML_RESPONSE && $content) {
            $rep = [];
            $match = false;
            $matchs = null;
            if (is_int(stripos($content, '<textarea'))) {
                $match = preg_match_all('#<textarea(.*?)>(.*?)</textarea>#is', $content, $matchs);
                if ($match) {
                    foreach ($matchs[0] as $match) {
                        $rep[] = '#___' . RandomName::generate('sisisisi') . '___#';
                    }
                    $content = str_replace($matchs[0], $rep, $content);
                }
            }
            $match_code = false;
            $matchs_code = null;
            if (is_int(stripos($content, '<code'))) {
                $match_code = preg_match_all('#<code(.*?)>(.*?)</code>#is', $content, $matchs_code);
                if ($match_code) {
                    foreach ($matchs_code[0] as $match_code) {
                        $rep[] = '#___' . RandomName::generate('sisisisi') . '___#';
                    }
                    $content = str_replace($matchs_code[0], $rep, $content);
                }
            }
            $content = preg_replace('#> <#', '><', preg_replace('# {2,}#', ' ', preg_replace('/[\n\r]|/', '', $content)));
            if ($match) {
                $content = str_replace($rep, $matchs[0], $content);
            }
            if ($match_code) {
                $content = str_replace($rep, $matchs_code[0], $content);
            }
        }
    }

}