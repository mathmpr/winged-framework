<?php

class Request
{
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

    public static $CHARSET = 'utf-8';

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
    public $using_cacert = 'No verefy ssl';
    private $ioptions = null;

    public function __construct($url = false, $params = [], $options = [], $ssl_verifypeer = false)
    {
        $this->build($url, $params, $options, $ssl_verifypeer);
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
            'contentType' => Request::$ACCEPT_HTML,
            'charset' => Request::$CHARSET,
            'type' => Request::$REQUEST_GET,
            'ignoreHeaders' => false,
        ];

        $ssl = false;

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
            $p = CoreString::removeSpaces(CoreString::removeAccents($u));
            if ($p != $u) {
                $url[$key] = $p;
            }
        }
        $url = join('/', $url);

        if (!function_exists('curl_init')) {
            CoreError::_die(__CLASS__, "cURL extension not found on this server.", __FILE__, __LINE__);
        } else {

            if ($url && filter_var($url, FILTER_VALIDATE_URL)) {

                $url_parse = parse_url($url);

                if ($url_parse['scheme'] === 'https') {
                    $ssl = true;
                }

                $this->url = $url;
                $this->ch = curl_init($this->url);
                curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($this->ch, CURLOPT_FILETIME, true);
                if ($ssl && $ssl_verifypeer === true) {
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
                        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
                        if (get_value_by_key('json_option', $this->ioptions) != null) {
                            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params, $this->ioptions['json_option']));
                        } else {
                            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));
                        }
                        $this->final_url = $url;
                    } else {
                        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
                        $this->final_url = $url;
                    }
                } else if (get_value_by_key('type', $this->ioptions) == 'get') {
                    if ($params && is_array($params) && !empty($params)) {
                        $this->final_url = $url . '?' . http_build_query($params);
                        curl_setopt($this->ch, CURLOPT_URL, $this->final_url);
                    } else {
                        $this->final_url = $url;
                        curl_setopt($this->ch, CURLOPT_URL, $url);
                    }
                }

                if (get_value_by_key('ignoreHeaders', $this->ioptions) !== null) {
                    if (get_value_by_key('ignoreHeaders', $this->ioptions) === false) {
                        $content_type = 'Content-type: ' . $this->ioptions['contentType'] . '; charset=' . $this->ioptions['charset'] . '';
                        $add = [$content_type];
                        if ($this->ioptions['contentType'] == 'aplication/json') {
                            if (get_value_by_key('json_option', $this->ioptions) != null) {
                                $add[] = 'Content-Length: ' . strlen(json_encode($params, $this->ioptions['json_option']));
                            } else {
                                $add[] = 'Content-Length: ' . strlen(json_encode($params));
                            }
                        }
                        $this->headers = array_merge($this->ioptions['headers'], $add);
                        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
                    } else {
                        $this->headers = ['cURL without headers'];
                        curl_setopt($this->ch, CURLOPT_HEADER, 0);
                    }
                }

                if ($ssl_verifypeer === true) {
                    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
                } else {
                    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
                }
            } else {
                CoreError::push(__CLASS__, "You can't make a resquest without a invalid ou empty URL", __FILE__, __LINE__);
            }
        }
        $this->parse_response = get_value_by_key('parseResponse', $this->ioptions);
        $this->ssl_verifypeer = $ssl_verifypeer;
        $this->last_options_before_send = $this->ioptions;
        return $this;
    }

    public function info()
    {
        return curl_getinfo($this->ch);
    }

    public function send()
    {
        return new Response($this->ch, $this);
    }

}