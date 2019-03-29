<?php

namespace Winged\External\Mailgun;

use Winged\Http\Request;

class Mailgun
{

    public $domain = null;
    public $api_key = null;
    public $resquest = null;


    public function __construct($api_key = false, $domain = false)
    {
        if (is_string($api_key) && is_string($domain)) {
            $this->domain = $domain;
            $this->api_key = $api_key;
        }
    }

    protected function buildBase()
    {
        return 'https://api:' . $this->api_key . '@api.mailgun.net/v3/' . $this->domain . '/';
    }

    protected function buildStdBase()
    {
        return 'https://api:' . $this->api_key . '@api.mailgun.net/v3/';
    }

    protected function clear()
    {
        $this->resquest = null;
    }

    public function validEmail($email = ''){
        $this->clear();
        $this->resquest = new Request($this->buildStdBase() . 'address/private/validate', [
            'address' => $email,
            'mailbox_verification' => true,
        ], [
            'type' => 'get',
            'accept' => Request::ACCEPT_JSON
        ], false);
        return $this->resquest->send()->output();
    }

    /**
     * @param array $params
     * @return mixed|null
     */
    public function sendMessage($params = [])
    {
        $this->clear();
        $this->resquest = new Request($this->buildBase() . 'messages', $params, [
            'type' => 'post',
            'accept' => Request::ACCEPT_JSON
        ], false);
        return $this->resquest->send()->output();
    }

    /**
     * @param bool $webhook_name
     * @return bool|mixed|null
     */
    public function getWebhooks($webhook_name = false)
    {
        $this->clear();
        $this->resquest = new Request($this->buildStdBase() . 'domains/' . $this->domain . '/webhooks', [], [
            'type' => 'get',
            'accept' => Request::ACCEPT_JSON
        ], false);
        $response = $this->resquest->send();
        if ($webhook_name && $response->ok()) {
            foreach ($response->output()['webhooks'] as $key => $webhook) {
                if ($webhook_name === $key) {
                    return $webhook;
                }
            }
            return false;
        } else {
            return $response->output();
        }
    }

    public function updateWebhook()
    {

    }

    public function insertWebhook()
    {

    }

    public function deleteWebhook()
    {

    }

}