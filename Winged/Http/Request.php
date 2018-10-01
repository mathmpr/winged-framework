<?php

namespace Winged\Http;

use Winged\Formater\Formater;
use Winged\Error\Error;

class Request
{
    public static $REQUEST_IS_JSON = 'application/json';
    public static $REQUEST_IS_JSONP = 'application/javascript';
    public static $REQUEST_IS_HTML = 'text/html';
    public static $REQUEST_IS_PLAIN = 'text/plain';
    public static $REQUEST_IS_XML = 'application/xml';
    public static $REQUEST_IS_XHML = 'application/xhml';
    public static $REQUEST_IS_SVG = 'application/xhml';
    public static $REQUEST_IS_SVG_XML = 'image/svg+xml';
    public static $REQUEST_IS_TIFF = 'image/tiff';
    public static $REQUEST_IS_BMP = 'image/bmp';
    public static $REQUEST_IS_JPG = 'image/jpg';
    public static $REQUEST_IS_JPEG = 'image/jpeg';
    public static $REQUEST_IS_PNG = 'image/png';
    public static $REQUEST_IS_GIF = 'image/gif';
    public static $REQUEST_IS_CSS = 'text/css';
    public static $REQUEST_IS_JAVASCRIPT = 'application/javascript';
    public static $REQUEST_IS_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    public static $ACCEPT_JSON = 'application/json';
    public static $ACCEPT_JSONP = 'application/javascript';
    public static $ACCEPT_HTML = 'text/html';
    public static $ACCEPT_PLAIN = 'text/plain';
    public static $ACCEPT_XML = 'application/xml';
    public static $ACCEPT_XHML = 'application/xhml';
    public static $ACCEPT_SVG = 'application/xhml';
    public static $ACCEPT_SVG_XML = 'image/svg+xml';
    public static $ACCEPT_TIFF = 'image/tiff';
    public static $ACCEPT_BMP = 'image/bmp';
    public static $ACCEPT_JPG = 'image/jpg';
    public static $ACCEPT_JPEG = 'image/jpeg';
    public static $ACCEPT_PNG = 'image/png';
    public static $ACCEPT_GIF = 'image/gif';
    public static $ACCEPT_CSS = 'text/css';
    public static $ACCEPT_JAVASCRIPT = 'application/javascript';
    public static $ACCEPT_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    public static $ACCEPT_ALL = '*/*';

    public static $CHARSET_REQUEST = 'utf-8';
    public static $CHARSET_ACCEPT = 'utf-8';

    public static $REQUEST_GET = 'get';
    public static $REQUEST_POST = 'post';
    public static $REQUEST_PUT = 'put';
    public static $REQUEST_DELETE = 'delete';

    public static $PARSE_RESPONSE_YES = true;
    public static $PARSE_RESPONSE_NO = false;

    public $url = false;
    public $final_url = false;
    public $ch = null;
    public $parse_response = true;
    public $ssl_verifypeer = false;
    public $cacert = 'https://curl.haxx.se/ca/cacert.pem';
    public $headers = false;
    public $last_options_before_send = false;
    public $using_cacert = 'No verefy SSL';
    public $is_ssl = false;
    public $params = [];
    public $ioptions = null;

    public function __construct($url = false, $params = [], $options = [], $ssl_verifypeer = false)
    {
        $this->build($url, $params, $options, $ssl_verifypeer);
    }

    public function setRequestMethod($method = 'get')
    {
        if (!is_string($method)) {
            $method = 'get';
        }
        $this->ioptions['type'] = $method;
        return $this;
    }

    public function setHeaders($headers = [])
    {
        if (!is_array($headers)) {
            $headers = [];
        }
        $this->ioptions['header'] = $headers;
        return $this;
    }

    public function addHeader($header = '')
    {
        if (is_string($header)) {
            $this->ioptions['headers'][] = $header;
        }
        return $this;
    }

    public function enableSsl()
    {
        $this->ssl_verifypeer = true;
        return $this;
    }

    public function disableSsl()
    {
        $this->ssl_verifypeer = false;
        return $this;
    }

    public function build($url = false, $params = [], $options = [], $ssl_verifypeer = false)
    {
        $this->ioptions = [
            'headers' => [
                'Connection: keep-alive',
                'Cache-Control: max-age=0',
                'Upgrade-Insecure-Requests: 1',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36'
            ],
            'parseResponse' => Request::$PARSE_RESPONSE_YES,
            'contentType' => Request::$REQUEST_IS_X_WWW_FORM_URLENCODED,
            'charsetRequest' => Request::$CHARSET_REQUEST,
            'accept' => Request::$ACCEPT_ALL,
            'type' => Request::$REQUEST_GET,
            'ignoreHeaders' => false,
        ];

        if ($this->ch !== null) {
            curl_close($this->ch);
        }

        foreach ($options as $key => $option) {
            if (get_value_by_key($key, $this->ioptions) !== null) {
                $this->ioptions[$key] = $option;
            }
        }

        $url = explode('/', $url);
        foreach ($url as $key => $u) {
            $p = Formater::removeSpaces(Formater::removeAccents($u));
            if ($p != $u) {
                $url[$key] = $p;
            }
        }
        $url = join('/', $url);
        if (!function_exists('curl_init')) {
            Error::_die(__CLASS__, "cURL extension not found on this server.", __FILE__, __LINE__);
        } else {
            if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
                $url_parse = parse_url($url);
                if ($url_parse['scheme'] === 'https') {
                    $this->is_ssl = true;
                }
                $this->url = $url;
                $this->ch = curl_init($this->url);
                $this->parse_response = get_value_by_key('parseResponse', $this->ioptions);
                $this->ssl_verifypeer = $ssl_verifypeer;
                $this->last_options_before_send = $this->ioptions;
                $this->params = $params;
                return $this;
            } else {
                Error::push(__CLASS__, "You can't make a resquest without a invalid ou empty URL", __FILE__, __LINE__);
            }
        }
        return false;
    }

    public function info()
    {
        if ($this->ch) {
            return curl_getinfo($this->ch);
        }
        return null;
    }

    public function send()
    {
        if ($this->ch) {

            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_FILETIME, true);
            if ($this->is_ssl && $this->ssl_verifypeer === true) {
                if (!file_exists('./cacert.pem')) {
                    ini_set('allow_url_fopen', 1);
                    $content = @file_get_contents($this->cacert);
                    $handle = fopen('./cacert.pem', 'w+');
                    fwrite($handle, $content);
                    fclose($handle);
                    curl_setopt($this->ch, CURLOPT_CAINFO, getcwd() . '/cacert.pem');
                    $this->using_cacert = 'Created and using.';
                } else {
                    $this->using_cacert = 'Found and using.';
                    curl_setopt($this->ch, CURLOPT_CAINFO, getcwd() . '/cacert.pem');
                }
                curl_setopt($this->ch, CURLOPT_SSLVERSION, 3);
            }
            if (get_value_by_key('type', $this->ioptions) == 'post') {
                if ($this->ioptions['contentType'] == 'application/json') {
                    curl_setopt($this->ch, CURLOPT_POST, true);
                    if (get_value_by_key('json_option', $this->ioptions) != null) {
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($this->params, $this->ioptions['json_option']));
                    } else {
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($this->params));
                    }
                    $this->final_url = $this->url;
                } else {
                    curl_setopt($this->ch, CURLOPT_POST, true);
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
                    $this->final_url = $this->url;
                }
            } else if (get_value_by_key('type', $this->ioptions) == 'get') {
                if ($this->params && is_array($this->params) && !empty($this->params)) {
                    $this->final_url = $this->url . '?' . http_build_query($this->params);
                    curl_setopt($this->ch, CURLOPT_URL, $this->final_url);
                } else {
                    $this->final_url = $this->url;
                    curl_setopt($this->ch, CURLOPT_URL, $this->url);
                }
            } else {
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->ioptions['type']));
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
            }

            $ignoreHeaders = get_value_by_key('ignoreHeaders', $this->ioptions);

            if ($ignoreHeaders !== null && $ignoreHeaders === false) {
                $content_type = 'Content-type: ' . $this->ioptions['contentType'] . '; charset=' . $this->ioptions['charsetRequest'] . '';
                $add = [$content_type];
                if ($this->ioptions['contentType'] == 'aplication/json') {
                    if (get_value_by_key('json_option', $this->ioptions) != null) {
                        $add[] = 'Content-Length: ' . strlen(json_encode($this->params, $this->ioptions['json_option']));
                    } else {
                        $add[] = 'Content-Length: ' . strlen(json_encode($this->params));
                    }
                }
                $add[] = 'Accept: ' . $this->ioptions['accept'];
                $add[] = 'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7';
                $this->headers = array_merge($this->ioptions['headers'], $add);
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
            } else {
                $this->headers = ['cURL without Headers'];
                curl_setopt($this->ch, CURLOPT_HEADER, 0);
            }

            if ($this->ssl_verifypeer === true) {
                curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
            } else {
                curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            }

            return new Response($this->ch, $this);
        }
        return null;
    }

}