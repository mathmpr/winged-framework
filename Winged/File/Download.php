<?php

namespace Winged\Download;

use Winged\Error\Error;
use Winged\Buffer\Buffer;

class Download {

    public static function download($path) {
        if (file_exists($path)) {
            $fn = explode("/", $path);
            Buffer::kill();
            header_remove();
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($path));
            header("Content-Disposition: attachment; filename=" . basename(end($fn)));
            readfile($path);
            exit;
        } else {
            Error::_die("File not found in: '" . $path . "'",false, __FILE__, __LINE__);
        }
    }

    public static function downloadAnyContent($content, $filename){
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename  . '";');
        exit($content);
    }

    public static function downloadAnyContentTxt($content, $filename){
        $end = explode('.', $filename);
        $o_end = array_pop($end);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . implode('.', $end) . '_conv.' . $o_end . '.txt";');
        exit($content);
    }
}