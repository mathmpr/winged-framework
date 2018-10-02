<?php

namespace Winged\Route;

use Winged\Buffer\Buffer;
use Winged\Date\Date;
use Winged\Error\Error;
use Winged\File\File;
use Winged\Http\Session;
use Winged\Utils\RandomName;
use Winged\Utils\WingedLib;
use Winged\Winged;
use Winged\WingedConfig;

/**
 * Class Route
 * @package Winged\Route
 */
class RouteExec extends Route
{
    public static function sendErrorResponse()
    {
        if (!empty(self::$response) && !empty($routes)) {
            Error::clear();
            Buffer::kill();
            header_remove();
            switch (self::$response['response']) {
                case 502:
                    header('HTTP/1.0 ' . self::$response['response'] . ' Bad Gateway');
                    break;
                case 401:
                    header('HTTP/1.0 ' . self::$response['response'] . ' Unauthorized');
                    break;
                case 404:
                    header('HTTP/1.0 ' . self::$response['response'] . ' Not Found');
                    $file = new File(WingedConfig::$NOTFOUND, false);
                    if ($file->exists()) {
                        Buffer::reset();
                        include_once WingedConfig::$NOTFOUND;
                        Buffer::flushKill();
                        exit;
                    }
                    break;
                case 200:
                    header('HTTP/1.0 ' . self::$response['response'] . ' OK');
                    break;
            }

            $headers = getallheaders();
            $accept = isset($headers['Accept']) ? $headers['Accept'] : 'application/json';
            switch ($accept) {
                case 'text/plain':
                    $accept = 'text';
                    break;
                case 'text/xml':
                    $accept = 'xml';
                    break;
                case 'application/json':
                    $accept = 'json';
                    break;
                case '*/*':
                    $accept = 'json';
                    break;
                default:
                    $accept = 'json';
                    break;
            }
            if ($accept === 'json') {
                header('Content-Type: application/json; charset=UTF-8');
                print json_encode(self::$response['content']);
            } else if ($accept === 'xml') {
                header('Content-Type: text/xml; charset=UTF-8');
                $xml = new \SimpleXMLElement('<response/>');
                self::arrayToXml(self::$response['content'], $xml);
                print $xml->asXML();
            } else if ($accept === 'text') {
                header('Content-Type: text/plain; charset=UTF-8');
                print_r(self::$response['content']);
            } else {
                header('Content-Type: application/json; charset=UTF-8');
                print json_encode(self::$response['content']);
            }
            exit;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function execute()
    {
        $headers = \getallheaders();
        $accept = isset($headers['Accept']) ? $headers['Accept'] : 'application/json';
        switch ($accept) {
            case 'text/plain':
                $accept = 'text';
                break;
            case 'text/xml':
                $accept = 'xml';
                break;
            case 'application/json':
                $accept = 'json';
                break;
            case '*/*':
                $accept = 'json';
                break;
            default:
                $accept = 'json';
                break;
        }
        /**
         * @var $route Route
         */
        $uri = explode('/', WingedLib::dotslash(Winged::$uri));
        $times = 0;
        foreach (self::$routes as $register => $route) {
            if (count7(Route::$part[$route->name]->uri) === count7($uri)) {
                $times++;
                $index = 0;
                $break = false;
                //search for names in uri and compare with registred routes
                foreach (self::$part[$register]->uri as $key => $value) {
                    if ($value['type'] === 'name') {
                        if ($value['name'] !== $uri[$index]) {
                            $break = true;
                            break;
                        }
                    }
                    $index++;
                }
                if ($break) {
                    $times--;
                    continue;
                }

                self::$part[$register]->valid = true;

                $index = 0;
                $args = [];
                foreach (self::$part[$register]->uri as $key => $value) {
                    if ($value['type'] === 'arg') {
                        if (array_key_exists($key, self::$part[$register]->rules)) {
                            if (is_string(self::$part[$register]->rules[$key])) {
                                preg_match('/' . self::$part[$register]->rules[$key] . '/', $uri[$index], $matches);
                                if (count7($matches) <= 0) {
                                    self::$part[$register]->errors['rule'][$key] = 'value ' . $uri[$index] . ' not match with rule ' . self::$part[$register]->rules[$key];
                                    $break = true;
                                    break;
                                }
                            } else if (is_callable(self::$part[$register]->rules[$key])) {
                                $continue = self::$part[$register]->rules[$key]($uri[$index]);
                                if (!$continue) {
                                    $break = true;
                                    break;
                                }
                            }
                        }
                        $args[] = $uri[$index];
                    }
                    $index++;
                }

                if ($break) {
                    self::$part[$register]->_502 = true;
                    continue;
                }

                if (is_post() && self::$part[$register]->http != 'post') {
                    self::$part[$register]->_502 = 'The route can\'t respond with found method in your http protocol.';
                    continue;
                }

                if (is_put() && self::$part[$register]->http != 'put') {
                    self::$part[$register]->_502 = 'The route can\'t respond with found method in your http protocol.';
                    continue;
                }

                if (is_get() && self::$part[$register]->http != 'get') {
                    self::$part[$register]->_502 = 'The route can\'t respond with found method in your http protocol.';
                    continue;
                }

                if (is_delete() && self::$part[$register]->http != 'delete') {
                    self::$part[$register]->_502 = 'The route can\'t respond with found method in your http protocol.';
                    continue;
                }

                if (!self::$part[$register]->_401 && !self::$part[$register]->_502) {
                    Error::clear();
                    Buffer::kill();
                    header_remove();
                    header('HTTP/1.0 200 Ok');
                    $return = null;
                    if (self::$part[$register]->callable) {
                        $return = call_user_func_array(self::$part[$register]->callable, $args);
                    } else if(self::$part[$register]->class){
                        $return = call_user_func_array([self::$part[$register]->class, self::$part[$register]->method], $args);
                    }else{
                        $token = RandomName::generate('sisisisisisi', true, false);
                        $expires = isset(self::$part[$register]->createSessionOptions['expires']) ? self::$part[$register]->createSessionOptions['expires'] : 3600;
                        $session = [
                            'create_time' => (new Date())->dmy(),
                            'expires' => $expires
                        ];
                        Session::set($token, $session);
                        $response = [
                            'token' => [
                                'name' => $token,
                                'expires' => $expires
                            ]
                        ];
                        if ($accept === 'json') {
                            header('Content-Type: application/json; charset=UTF-8');
                            print json_encode($response);
                        } else if ($accept === 'xml') {
                            header('Content-Type: text/xml; charset=UTF-8');
                            $xml = new \SimpleXMLElement('<response/>');
                            self::arrayToXml($response, $xml);
                            print $xml->asXML();
                        } else if ($accept === 'text') {
                            header('Content-Type: text/plain; charset=UTF-8');
                            print_r($response);
                        } else {
                            header('Content-Type: application/json; charset=UTF-8');
                            print json_encode($response);
                        }
                        exit;
                    }
                    if (is_array($return)) {
                        if ($accept === 'json') {
                            header('Content-Type: application/json; charset=UTF-8');
                            print json_encode($return);
                        } else if ($accept === 'xml') {
                            header('Content-Type: text/xml; charset=UTF-8');
                            $xml = new \SimpleXMLElement('<response/>');
                            self::arrayToXml($return, $xml);
                            print $xml->asXML();
                        } else if ($accept === 'text') {
                            header('Content-Type: text/plain; charset=UTF-8');
                            print_r($return);
                        } else {
                            header('Content-Type: application/json; charset=UTF-8');
                            print json_encode($return);
                        }
                        exit;
                    }
                    return true;
                }
            }
            return false;
        }

        if ($times === 0) {
            self::registerErrorResponse([
                'response' => 404,
                'content' => [
                    'data' => '404! No route or controller was able to service your request.'
                ]
            ]);
            return false;
        } else {
            foreach (self::$routes as $route) {
                if (Route::$part[$route->name]->valid) {
                    if (Route::$part[$route->name]->_401) {
                        self::registerErrorResponse([
                            'response' => 401,
                            'content' => [
                                'data' => Route::$part[$route->name]->errors['unauthorized']
                            ]
                        ]);
                    } else if (is_string(Route::$part[$route->name]->_502)) {
                        self::registerErrorResponse([
                            'response' => 502,
                            'content' => [
                                'data' => Route::$part[$route->name]->_502
                            ]
                        ]);
                    } else if (is_bool(Route::$part[$route->name]->_502)) {
                        self::registerErrorResponse([
                            'response' => 502,
                            'content' => [
                                'data' => Route::$part[$route->name]->errors['rule']
                            ]
                        ]);
                    }
                }
            }
        }
        return false;
    }

}