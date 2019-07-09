<?php

namespace Winged\Frontend;

use Winged\File\File;
use Winged\Buffer\Buffer;
use Winged\Utils\RandomName;
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
     * @var $first_render bool
     */
    public $first_render = true;

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
                    echo $file->read();
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