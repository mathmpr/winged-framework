<?php

class CoreFiles
{

    public static function copy($from = '', $to = '')
    {
        $from = wl::dotslash($from, true);
        $from = substr($from, 0, -1);
        $to = wl::dotslash($to, true);
        $exp = explode('/', $from);
        $file = end($exp);
        if (file_exists($from)) {
            if (copy($from, $to . $file)) {
                return $to . $file;
            }
        }
        return false;
    }

    public static function unlink($file)
    {
        if (file_exists($file) && !is_dir($file)) {
            return unlink($file);
        }
        return false;
    }

    public static function rmdir($dir)
    {
        if (file_exists($dir) && is_dir($dir)) {
            return rmdir($dir);
        }
        return false;
    }

    public static function renamePreserveExtension($path, $name)
    {
        $expf = explode('/', $path);
        $end = array_pop($expf);
        $exp = explode('.', $end);
        $new_name = $name . '.' . end($exp);
        $npath = implode('/', $expf);
        $npath = wl::dotslash(wl::dotslash($npath), true);
        $npath = $npath . $new_name;
        rename($path, $npath);
        if (file_exists($path) && !is_dir($path)) {
            unlink($path);
            return $npath;
        }
        return $npath;
    }

    public static function dinamicMkdir($folder)
    {
        $exp = explode('/', $folder);
        $folders = [];
        $first = true;
        $count = 0;
        foreach ($exp as $folder) {
            if ($folder != '' && $folder != '/') {
                if ($first) {
                    $first = false;
                    $folders[] = './' . $folder;
                } else {
                    $folders[] = $folders[$count] . '/' . $folder;
                    $count++;
                }
            }
        }

        foreach ($folders as $folder) {
            if (!file_exists($folder) && !is_dir($folder)) {
                mkdir($folder);
            }
        }

    }

    public static function clearDir($folder)
    {
        $folder = wl::dotslash(wl::dotslash($folder), true);
        $scan = scandir($folder);
        foreach ($scan as $file) {
            if ($file != '.' && $file != '..') {
                $join_f = $folder . $file;
                if (is_dir($join_f)) {
                    self::clearDir($join_f);
                    self::rmdir($join_f);
                } else if (is_file($join_f) && file_exists($join_f)) {
                    self::unlink($join_f);
                }
            }
        }
    }

    public static function clearDirRemoveSelf($folder)
    {
        try {
            self::clearDir($folder);
            self::rmdir($folder);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getExtension($path){
        $exp = explode('.', $path);
        $end = array_pop($exp);
        return $end;
    }

}

