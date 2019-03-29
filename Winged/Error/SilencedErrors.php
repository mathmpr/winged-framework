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
            'gzdecode' => [
                'warning' => false,
                'fatal' => false
            ],
            'gzinflate' => [
                'warning' => false,
                'fatal' => false
            ],
        ];
    }
}