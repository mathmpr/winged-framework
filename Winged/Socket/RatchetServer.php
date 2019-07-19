<?php

namespace Winged\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use Winged\Database\Connections;
use Winged\Winged;

class RatchetServer implements MessageComponentInterface
{

    public $server = null;

    public $loop = null;

    public $socket = null;

    public $say_hello = null;

    public $hello_message = null;

    public $clients = null;
    /**
     * @var $current ConnectionInterface
     */
    public $current = null;

    public $port = null;

    public $not_found_method = null;

    private $methods = [];

    public function __construct($port = 2080, $say_hello = false)
    {
        set_time_limit(0);

        $this->clients = new \SplObjectStorage;

        $this->port = $port;

        $this->loop = Factory::create();

        $this->socket = new \React\Socket\Server('0.0.0.0:' . $this->port, $this->loop);

        if (is_bool($say_hello)) {
            $this->say_hello = $say_hello;
        } else {
            $this->say_hello = false;
        }

        $this->server = new IoServer(
            new HttpServer(
                new WsServer(
                    $this
                )
            ),
            $this->socket,
            $this->loop
        );
    }

    public function onOpen(ConnectionInterface $client)
    {
        $this->clients->attach($client);
        $this->current = $client;
        if ($this->say_hello && $this->hello_message) {
            $client->send($this->hello_message);
        }
    }

    public function sendForAll($message)
    {
        if (is_string($message)) {
            $message = json_encode([
                'data' => [
                    'message' => $message
                ]
            ]);
        } else if (is_array($message)) {
            $message = json_encode($message);
        }
        foreach ($this->clients as $key => $client) {
            /**
             * @var $client ConnectionInterface
             */
            $client->send($message);
        }
        return true;
    }

    public function send($message)
    {
        if (is_string($message)) {
            $message = json_encode([
                'data' => [
                    'message' => $message
                ]
            ]);
        } else if (is_array($message)) {
            $message = json_encode($message);
        }
        if ($this->current) {
            $this->current->send($message);
            return true;
        }
        return false;
    }

    public function currentClose()
    {
        if ($this->current) {
            $this->current->close();
            return true;
        }
        return false;
    }

    public function current()
    {
        return $this->current;
    }

    public function fallClients()
    {
        $this->socket->removeAllListeners();
    }

    public function resume()
    {
        $this->socket->resume();
    }

    public function pause()
    {
        $this->socket->pause();
    }

    public function close($message = false)
    {
        $initial = time();
        $this->loop->addPeriodicTimer(0.2, function () use ($initial, $message) {
            $date = time();
            foreach ($this->clients as $client) {
                $this->current = $client;
                $this->send($message);
                $this->clients->detach($client);
            }
            if (($date - $initial) >= 5) {
                $this->socket->close();
                Winged::_exit();
            }
        });
    }

    public function stopLoop()
    {
        $this->loop->stop();
    }

    public function resumeLoop()
    {
        $this->loop->run();
    }

    private function makeBoolString(&$array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->makeBoolString($value);
            }
            if (is_bool($value)) {
                if ($value === false) {
                    $array[$key] = 'false';
                } else {
                    $array[$key] = 'true';
                }
            }
        }
    }

    public function onMessage(ConnectionInterface $client, $message)
    {
        if (strlen($message) > 0) {
            $json = json_decode($message, true);
            if (is_array($json)) {
                if (array_key_exists('call', $json)) {
                    $call = $json['call'];
                    unset($json['call']);
                    if (array_key_exists($call, $this->methods)) {
                        $this->current = $client;
                        $return = call_user_func_array($this->methods[$call], $json);
                        if (!is_array($return) && $return != null) {
                            $return = [
                                'data' => [
                                    'message' => $return
                                ]
                            ];
                        }
                        if ($return != null) {
                            $this->makeBoolString($return);
                            $client->send(json_encode($return));
                        }
                    } else {
                        if ($this->not_found_method) {
                            $return = call_user_func($this->not_found_method);
                            if (is_array($return)) {
                                $client->send(json_encode($return));
                            }
                        } else {
                            $client->send(json_encode([
                                'data' => [
                                    'message' => 'Endpoint not found in this server. Please see docs for use a valid endpoint.',
                                    'method_recevied' => $call,
                                    'clients_count' => $this->clients->count()
                                ]
                            ]));
                        }
                    }
                } else {
                    $response = json_encode([
                        'data' => [
                            'message' => 'Please send a valid message. key [call] not found in your message.',
                            'recevied' => $message,
                            'clients_count' => $this->clients->count()
                        ]
                    ]);
                    $client->send($response);
                }
            } else {
                $response = json_encode([
                    'data' => [
                        'message' => 'Please send a valid message in JSON format.',
                        'recevied' => $message,
                        'clients_count' => $this->clients->count()
                    ]
                ]);
                $client->send($response);
            }
        }
    }

    public function onClose(ConnectionInterface $client)
    {
        $this->clients->detach($client);
    }

    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        $connection->close();
    }

    public function onNotFoundMethod($callback)
    {
        if (is_callable($callback)) {
            $this->not_found_method = \Closure::bind($callback, $this, get_class());
        }
    }

    public function setDefaultHelloMessage($message)
    {
        if (is_string($message)) {
            $this->hello_message = $message;
            $this->say_hello = true;
        } else if (is_callable($message)) {
            $message = call_user_func($message);
            if (is_string($message)) {
                $this->say_hello = true;
                $this->hello_message = $message;
            }
        }
        if ($this->say_hello) {
            if (is_string($this->hello_message)) {
                $this->hello_message = json_encode([
                    'data' => [
                        'message' => $this->hello_message
                    ]
                ]);
            }
        }
    }

    public function register($method, $function)
    {
        if (is_callable($function) && is_string($method)) {
            $this->methods[$method] = \Closure::bind($function, $this, get_class());
        }
    }

    public function loop()
    {
        $this->server->run();
    }

    public function __call($name, $arguments)
    {
        foreach ($this->methods as $key => $method) {
            if ($key === $name) {
                return call_user_func_array($this->methods[$key], $arguments);
            }
        }
        return false;
    }


}