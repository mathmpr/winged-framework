<?php

namespace Winged\Error;

/**
 * Class SilencedErrors
 * @package Winged\Error
 */
class SilencedErrors
{
    /**
     * @return array
     */
    public static function getListOfSilencedFunctions()
    {
        return [
            'exif_read_data' => [
                'warning' => false,
                'fatal' => false
            ],
            'json_encode' => [
                'warning' => false,
                'fatal' => false
            ],
            'json_decode' => [
                'warning' => false,
                'fatal' => false
            ],
            'fopen' => [
                'warning' => false,
                'fatal' => false
            ],
            'fclose' => [
                'warning' => false,
                'fatal' => false
            ],
            'is_file' => [
                'warning' => false,
                'fatal' => false
            ],
            'is_dir' => [
                'warning' => false,
                'fatal' => false
            ],
            'date' => [
                'warning' => false,
                'fatal' => false
            ],
            'strtotime' => [
                'warning' => false,
                'fatal' => false
            ],
            //'stream_socket_client' => [
            //    'warning' => false,
            //    'fatal' => false
            //],
            //'fsockopen' => [
            //    'warning' => false,
            //    'fatal' => false
            //],
        ];
    }
}