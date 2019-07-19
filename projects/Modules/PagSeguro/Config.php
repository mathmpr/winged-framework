<?php

namespace PagSeguro;

use Winged\Controller\Controller;
use Winged\Http\Request;

/**
 * PagSeguro configuration class
 * Class Config
 * @package PagSeguro
 */
class Config
{

    /**
     * @var $token string : Is your access token to API
     * the token may be different if you are in sandbox mode
     */
    public static $token = '15D2E823B8C04A9DAF995DCAB4EDF997';

    /**
     * @var $token string : Is a ID of application, your acount can have more than one application
     */
    public static $applicationId = '';

    /**
     * @var $receiverEmail string : specifies the email that will receive the payment, this email is the same one used to login to the PagSeguro panel
     */
    public static $receiverEmail = 'matheusprador@gmail.com';

    /**
     * @var string
     */
    public static $paymantMode = 'default';

    /**
     * @var int
     */
    public static $sandbox = false;

    /**
     * Set mode to sandbox
     * @param string $token
     */
    public static function isSandBox($token = '')
    {
        if ($token != '') {
            self::$token = $token;
        }
        self::$sandbox = 'sandbox.';
    }

    /**
     * return token
     * @return string
     */
    public static function getToken()
    {
        return self::$token;
    }

    /**
     * return payment mode
     * @return string
     */
    public static function getPaymentMode()
    {
        return self::$paymantMode;
    }

    /**
     * return email
     * @return string
     */
    public static function getEmail()
    {
        return self::$receiverEmail;
    }

    /**
     * return sandbox
     * @return string
     */
    public static function getSandbox()
    {
        return self::$sandbox;
    }

    /**
     * return application id
     * @return string
     */
    public static function getApplicationId()
    {
        return self::$applicationId;
    }

    /**
     * adds the javascript required for integration to take place
     * @param Controller $controller
     */
    public static function addRequiredJs(Controller $controller)
    {
        $request = new Request('https://ws.' . self::getSandbox() . 'pagseguro.uol.com.br/v2/sessions?token='. self::getToken() .'&email='. self::getEmail() .'',
            [],
            [
                'type' => Request::$REQUEST_POST,
            ], false);
        $response = $request->send();
        if ($response->ok()) {
            $response = new \SimpleXMLElement($response->output());
            $controller->appendJs('pagseguro.sessionId', '<script>var PagSeguroSessionId = \''. $response->id .'\';</script>');
        }
        $controller->appendJs('pagseguro.directpayment', 'https://stc.' . self::getSandbox() . 'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js', [], true);
    }

}