<?php

namespace Winged\Model;

use Winged\Http\Session;
use Winged\Database\DbDict;
use Winged\Validator\Email\Email;
use Winged\Validator\Validator;

/**
 * class Newsletter
 * @package Winged\Model
 **/
class Newsletter extends Model
{
    /**
     * Newsletter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_email integer */
    public $id_email;
    /** @var $email string */
    public $email;

    /**
     * Returns the name of the database table that this model represents
     * @return string
     */
    public static function tableName()
    {
        return "newsletter";
    }

    /**
     * Returns the name of the primary key of target table
     * @return string
     */
    public static function primaryKeyName()
    {
        return "id_email";
    }

    /**
     * Returns ou set a value for primary key
     * @param bool $pk
     * @return $this|int|integer
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_email = $pk;
            return $this;
        }
        return $this->id_email;
    }

    /**
     * Set behaviors, this works when method load is called
     * In the array puts a key with same name of a property of this class and value is a funcion or anonymous function
     * The return of these functions rewrite the initial value of propertie
     * @return array
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Same syntax of behaviors
     * When you fetch value from database, these value as reversed
     * @return array
     */
    public function reverseBehaviors()
    {
        return [];
    }

    /**
     * Same syntax of behaviors, but value for key can be have an array os string
     * String for unic validation, array for more than one validations
     * Within the second array, the keys can have any name, but some names need to be true in the front because they already have predefined validation function
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'required' => true,
                'email' => true,
                'bounce' => function () {
                    //$mailgun = new \Mailgun('24c2bc3cf8f31334010eaf804ee30546-b0aac6d0-491086f4', 'pradoit.com.br');
                    //return $mailgun->validEmail($this->email)['is_valid'];
                    return Email::check($this->email, 'no-reply@gmail.com');
                },
                'db' => function () {
                    $model = (new Newsletter())
                        ->select()
                        ->from(['N' => Newsletter::tableName()])
                        ->where(ELOQUENT_EQUAL, ['N.email' => $this->email])
                        ->one();
                    if ($model) {
                        return false;
                    }
                    return true;
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'email' => [
                'required' => 'Esse campo é obrigatório.',
                'email' => 'Insira um e-mail válido.',
                'db' => 'Este e-mail já está cadastrado em nossa newslwtter. Que tal tentar com uma conta diferente?',
                'bounce' => 'Por favor, confirme o endereço de e-mail. Aparentemente, o que foi citado está inválido ou não existe.',
            ],
        ];
    }

    /**
     * Same syntax of messages where value of keys is an custom string
     * @return array
     */
    public function labels()
    {
        return [];
    }
}