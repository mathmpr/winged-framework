<?php
            
namespace Winged\Model;

/**
 * class FakeGa
 * @package Winged\Model
 **/
class FakeGa extends Model
{
    /**
     * FakeGa constructor.
     */
    public function __construct(){
        parent::__construct();
        return $this;
    }
    
    /** @var $id_ga integer */
    public $id_ga;
    /** @var $uri string */
    public $uri;
    /** @var $_group string */
    public $_group;
    /** @var $_page_name string */
    public $_page_name;
    /** @var $_sub_group string */
    public $_sub_group;
    /** @var $type string */
    public $type;
    /** @var $user_id string */
    public $user_id;
    /** @var $returned integer */
    public $returned;
    /** @var $from_url string */
    public $from_url;
    /** @var $init_time string */
    public $init_time;
    /** @var $final_time string */
    public $final_time;
    /** @var $session_id string */
    public $session_id;
    /** @var $location string */
    public $location;
    /** @var $estate string */
    public $estate;
    /** @var $country string */
    public $country;
    
    /**
     * Returns the name of the database table that this model represents
     * @return string
     */
    public static function tableName()
    {
        return "fake_ga";
    }
    
    /**
     * Returns the name of the primary key of target table
     * @return string
     */
    public static function primaryKeyName()
    {        
        return "id_ga";
    }
    
    /**
     * Returns ou set a value for primary key
     * @param bool $pk
     * @return $this|int|integer
     */
    public function primaryKey($pk = false)
    {        
        if($pk && (is_int($pk) || intval($pk) != 0)){
            $this->id_ga = $pk;
            return $this;        
        }        
        return $this->id_ga;    
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
        return [];
    }
    
    /**
     * Same syntax of rules
     * The value of final key inside array is an custom string
     * @return array
     */
    public function messages()
    {
        return [];
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