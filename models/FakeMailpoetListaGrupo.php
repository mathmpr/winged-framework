<?php
            
class FakeMailpoetListaGrupo extends Model
{
    public function __construct(){
        parent::__construct();
        return $this;
    }
    
    /** @var $id integer */
    public $id;
    /** @var $id_grupo integer */
    public $id_grupo;
    /** @var $id_lista integer */
    public $id_lista;
    
    public static function tableName()
    {
        return "fake_mailpoet_lista_grupo";
    }
    
    public static function primaryKeyName()
    {        
        return "id";
    }
    
    public function primaryKey($pk = false)
    {        
        if($pk && (is_int($pk) || intval($pk) != 0)){
            $this->id = $pk;
            return $this;        
        }        
        return $this->id;    
    }

    public function behaviors()
    {
        return [];
    }
    
    public function reverseBehaviors()
    {        
        return [];    
    }
    
    public function rules()
    {        
        return [];
    }
    
    public function messages()
    {
        return [];
    }
    
    public function labels()
    {        
        return [];    
    }
}