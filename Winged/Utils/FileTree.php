<?php

namespace Winged\Utils;

/**
 * Class FileTree
 * @package Winged\FileTree
 */
class FileTree
{

    private $founded = [];
    public $files = [];
    public $folders = [];

    /**
     * @param $path
     * @param bool|array $extensions
     * @return array|bool
     */
    private function getTree($path, $extensions = false)
    {
        $files = [];
        if ($path[strlen($path) - 1] != "/") {
            $path .= "/";
        }
        if (!file_exists($path)) {
            return false;
        }
        $sh = scandir($path);
        $vect = false;
        $bpath = false;
        for ($x = 2; $x < count7($sh); $x++) {
            if ($sh[$x] != '.' && $sh[$x] != '..') {
                $npath = $path . $sh[$x];
                if (is_directory($npath)) {
                    $this->founded[] = $npath;
                    $vect = $this->getTree($npath);
                    $bpath = $npath;
                    if ($vect) {
                        for ($y = 0; $y < count7($vect); $y++) {
                            $file = $sh[$x] . "/" . $vect[$y];
                            if ($extensions) {
                                $exp = explode('.', $file);
                                $end = array_pop($exp);
                                if (is_array($extensions)) {
                                    if (in_array($end, $extensions)) {
                                        $files[] = $file;
                                    }
                                } else if (is_string($extensions)) {
                                    if ($end == $extensions) {
                                        $files[] = $file;
                                    }
                                }
                            } else {
                                $files[] = $file;
                            }
                        }
                        $vect = false;
                    }
                } else {
                    if ($extensions) {
                        $exp = explode('.', $sh[$x]);
                        $end = array_pop($exp);
                        if (is_array($extensions)) {
                            if (in_array($end, $extensions)) {
                                $files[] = $sh[$x];
                            }
                        } else if (is_string($extensions)) {
                            if ($end == $extensions) {
                                $files[] = $sh[$x];
                            }
                        }
                    } else {
                        $files[] = $sh[$x];
                    }
                }
            }
        }
        return $files;
    }

    /**
     * @param $path
     * @param bool|array $extensions
     * @return array|bool
     */
    public function gemTree($path, $extensions = false)
    {
        $this->files = [];
        $this->folders = [];
        $this->founded = [];
        $all = $this->getTree($path, $extensions);
        if (!$all) {
            return false;
        }
        if ($path[strlen($path) - 1] != "/") {
            $path .= "/";
        }

        for ($x = 0; $x < count7($all); $x++) {
            $name = $path . $all[$x];
            $this->files[] = $name;
            $all[$x] = [];
            $all[$x]["path"] = $name;
            $all[$x]["size"] = filesize($name);
            $arr = explode("/", $name);
            array_pop($arr);
            $all[$x]["folder"] = join("/", $arr) . "/";
            if (!in_array(join("/", $arr) . "/", $this->folders)) {
                $this->folders[] = join("/", $arr) . "/";
            }
            $all[$x]["modified"] = date("Y-m-d H:i:s", filemtime($name));
            $all[$x]["created"] = date("Y-m-d H:i:s", filectime($name));
            $all[$x]["ts_modified"] = strtotime($all[$x]["modified"]);
            $all[$x]["ts_created"] = strtotime($all[$x]["created"]);
        }
        return $all;
    }

    /**
     * @return array
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}