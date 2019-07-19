<?php

namespace Winged\Route;

use Winged\Buffer\Buffer;
use Winged\Database\Connections;
use Winged\Date\Date;
use Winged\Error\Error;
use Winged\File\File;
use Winged\Http\HttpResponseHandler;
use Winged\Http\Session;
use Winged\Utils\RandomName;
use Winged\Utils\WingedLib;
use Winged\Winged;
use WingedConfig;

/**
 * Class Route
 * @package Winged\Route
 */
class RouteExec extends Route
{
    public static function sendErrorResponse()
    {
        if (!empty(self::$response) && !empty(self::$routes)) {
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
                    $file = new File(WingedConfig::$config->NOTFOUND, false);
                    if ($file->exists()) {
                        Buffer::reset();
                        include_once WingedConfig::$config->NOTFOUND;
                        Buffer::flushKill();
                        Winged::_exit();
                    }
                    break;
                case 200:
                    header('HTTP/1.0 ' . self::$response['response'] . ' OK');
                    break;
            }

            $responseHandler = new HttpResponseHandler();
            $headers = getallheaders();
            $accept = isset($headers['Accept']) ? $headers['Accept'] : 'application/json';
            switch ($accept) {
                case 'text/yaml':
                    $responseHandler->dispatchYaml(self::$response['content']);
                    break;
                case 'text/plain':
                    $responseHandler->dispatchTxt(self::$response['content']);
                    break;
                case 'text/xml':
                    $responseHandler->dispatchXml(self::$response['content']);
                    break;
                case 'application/json':
                    $responseHandler->dispatchJson(self::$response['content']);
                    break;
                case '*/*':
                    $responseHandler->dispatchJson(self::$response['content']);
                    break;
                default:
                    $responseHandler->dispatchJson(self::$response['content']);
                    break;
            }
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
            case 'text/yaml':
                $accept = 'yaml';
                break;
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
        $uri = WingedLib::explodePath(Winged::$uri);
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

                $origin = server('http_referer');

                if (!empty(self::$part[$register]->origins)) {
                    if (!in_array($origin, self::$part[$register]->origins)) {
                        self::$part[$register]->_401 = 'The server can\'t respond appropriately because the client server is not allowed to make requests to that endpoint. To learn more, see more about CORS policies.';
                        continue;
                    }
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
                        $return = call_user_func_array(self::$part[$register]->callable, array_merge($args, [self::$part[$register]->vars]));
                    } else if (self::$part[$register]->class) {
                        $return = call_user_func_array([self::$part[$register]->class, self::$part[$register]->method], array_merge($args, [self::$part[$register]->vars]));
                    } else {
                        $token = RandomName::generate('sisisisisisi', true, false);
                        $expires = isset(self::$part[$register]->createSessionOptions['expires']) ? self::$part[$register]->createSessionOptions['expires'] : 3600;
                        $session = [
                            'create_time' => (new Date())->dmy(),
                            'expires' => $expires
                        ];
                        Session::set($token, $session);

                        $response = [
                            'token' => [
                                'status' => true,
                                'name' => $token,
                                'expires' => $expires
                            ]
                        ];

                        $responseHandler = new HttpResponseHandler();
                        switch ($accept) {
                            case 'json':
                                $responseHandler->dispatchJson($response);
                                break;
                            case 'xml':
                                $responseHandler->dispatchXml($response);
                                break;
                            case 'text':
                                $responseHandler->dispatchTxt($response);
                                break;
                            case 'yaml':
                                $responseHandler->dispatchYaml($response);
                                break;
                            default:
                                $responseHandler->dispatchJson($response);
                                break;
                        }
                    }

                    if (is_array($return)) {
                        $responseHandler = new HttpResponseHandler();
                        switch ($accept) {
                            case 'json':
                                $responseHandler->dispatchJson($return, false);
                                break;
                            case 'xml':
                                $responseHandler->dispatchXml($return, false);
                                break;
                            case 'text':
                                $responseHandler->dispatchTxt($return, false);
                                break;
                            case 'yaml':
                                $responseHandler->dispatchYaml($return, false);
                                break;
                            default:
                                $responseHandler->dispatchJson($return, false);
                                break;
                        }
                    }
                    return $return;
                }
            }
        }

        foreach (self::$routes as $route) {
            if (Route::$part[$route->name]->valid) {
                if (Route::$part[$route->name]->_401) {
                    Route::$part[$route->name]->errors['rule']['status'] = false;
                    self::registerErrorResponse([
                        'response' => 401,
                        'content' => [
                            'data' => Route::$part[$route->name]->errors['unauthorized']
                        ]
                    ]);
                } else if (is_string(Route::$part[$route->name]->_502)) {
                    Route::$part[$route->name]->errors['rule']['status'] = false;
                    self::registerErrorResponse([
                        'response' => 502,
                        'content' => [
                            'data' => Route::$part[$route->name]->_502
                        ]
                    ]);
                } else if (is_bool(Route::$part[$route->name]->_502)) {
                    Route::$part[$route->name]->errors['rule']['status'] = false;
                    self::registerErrorResponse([
                        'response' => 502,
                        'content' => [
                            'data' => Route::$part[$route->name]->errors['rule']
                        ]
                    ]);
                }
            }
        }

        if (WingedConfig::$config->USE_404_WITH_ROUTES === true && $times === 0) {
            self::registerErrorResponse([
                'response' => 404,
                'content' => [
                    'status' => false,
                    'data' => [
                        'status' => false,
                        'notfound' => '404! No route or controller was able to service your request.'
                    ]
                ]
            ]);
        }
        return true;
    }
}