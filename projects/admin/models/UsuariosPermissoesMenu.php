<?php
            
use Winged\Model\Model;

/**
 * class UsuariosPermissoesMenu
 * @package Winged\Model
 **/
class UsuariosPermissoesMenu extends Model
{
    /**
     * UsuariosPermissoesMenu constructor.
     */
    public function __construct(){
        parent::__construct();
        return $this;
    }
    
    /** @var $id_perm_menu integer */
    public $id_perm_menu;

    /** @var $id_menu integer */
    public $id_menu;

    /** @var $id_usuario integer */
    public $id_usuario;

    
    /**
     * Returns the name of the database table that this model represents
     * @return string
     */
    public static function tableName()
    {
        return "usuarios_permissoes_menu";
    }
    
    /**
     * Returns the name of the primary key of target table
     * @return string
     */
    public static function primaryKeyName()
    {        
        return "id_perm_menu";
    }
    
    /**
     * Returns ou set a value for primary key
     * @param bool $pk
     * @return $this|int|integer
     */
    public function primaryKey($pk = false)
    {        
        if($pk && (is_int($pk) || intval($pk) != 0)){
            $this->id_perm_menu = $pk;
            return $this;        
        }        
        return $this->id_perm_menu;    
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