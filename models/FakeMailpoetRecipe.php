<?php class FakeMailpoetRecipe extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /** @var $id_recipe integer */
    public $id_recipe;
    /** @var $id_lista integer */
    public $id_lista;
    /** @var $id_pessoa integer */
    public $id_pessoa;
    /** @var $status integer */
    public $status;
    /** @var $recipe_date string */
    public $recipe_date;

    public static function tableName()
    {
        return "fake_mailpoet_recipe";
    }

    public static function primaryKeyName()
    {
        return "id_recipe";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_recipe = $pk;
            return $this;
        }
        return $this->id_recipe;
    }

    public function behaviors()
    {
        return [];
    }

    public function reverseBehaviors()
    {
        return [];
    }

    public function labels()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }
}