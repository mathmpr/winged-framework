<?php

use Winged\Model\Model;
use Winged\Formater\Formater;

/**
 * Class Slugs
 */
class Slugs extends Model
{

    public $id;

    public $slug = 'n-a';

    public $linkTo = 0;

    public $usedIn = 'without_table';

    /**
     * Slugs constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public static function primaryKeyName()
    {
        return 'id';
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'slugs';
    }

    /**
     * @param bool $pk
     *
     * @return $this|int|Model
     */
    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id = $pk;
            return $this;
        }
        return $this->id;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'slug' => \Winged\Formater\Formater::toUrl($this->slug)
        ];
    }

    /**
     * @return array
     */
    public function reverseBehaviors()
    {
        return [];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => [
                'required' => true,
                'select' => function () {
                    $one = $this->select()
                        ->from(['S' => Slugs::tableName()])
                        ->where(ELOQUENT_EQUAL, ['S.slug' => $this->slug])
                        ->one();
                    if ($one) {
                        return false;
                    }
                    return true;
                }
            ]
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'slug' => [
                'required' => 'Esse campo é obrigatório.',
                'select' => 'Já existe outro registro no sistema que utiliza esse mesmo nome para a URL.'
            ]
        ];
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [
            'slug' => 'Nome para a URI: '
        ];
    }


    /**
     * @param string $slug
     *
     * @return mixed|Model|null
     */
    public static function exists($slug = '')
    {
        $slugObject = new Slugs();
        $slug = Formater::toUrl($slug);
        $exists = $slugObject->select()
            ->from(['S' => Slugs::tableName()])
            ->where(ELOQUENT_EQUAL, ['S.slug' => $slug])
            ->one();

        return $exists;
    }

    /**
     * save slug into DB
     *
     * @param string $slug
     * @param int    $linkTo
     * @param string $usedIn
     *
     * @return bool | int
     */
    public static function pushSlug($slug = '', $linkTo = 0, $usedIn = '')
    {
        $slugObject = new Slugs();
        $slug = Formater::toUrl($slug);
        if (!Slugs::exists($slug)) {
            $slugExists = $slugObject->select()
                ->from(['S' => Slugs::tableName()])
                ->where(ELOQUENT_EQUAL, ['S.linkTo' => $linkTo])
                ->where(ELOQUENT_EQUAL, ['S.usedIn' => $usedIn])
                ->one();
            if ($slugExists) {
                $slugObject->update(['S' => Slugs::tableName()])
                    ->set([
                        'S.slug' => $slug,
                    ])
                    ->where(ELOQUENT_EQUAL, ['S.linkTo' => $linkTo])
                    ->where(ELOQUENT_EQUAL, ['S.usedIn' => $usedIn]);
            } else {
                $slugObject->insert()
                    ->into(Slugs::tableName())
                    ->values([
                        'slug' => $slug,
                        'linkTo' => $linkTo,
                        'usedIn' => $usedIn
                    ]);
            }
            return $slugObject->execute();
        }
        return false;
    }

    /**
     * @param int    $linkTo
     * @param string $usedIn
     *
     * @return $this|mixed|Model|null
     */
    public static function getSlug($linkTo = 0, $usedIn = '')
    {
        $slug = new Slugs();
        $one = $slug->select()
            ->from(['S' => Slugs::tableName()])
            ->where(ELOQUENT_EQUAL, ['S.linkTo' => $linkTo])
            ->where(ELOQUENT_EQUAL, ['S.usedIn' => $usedIn])
            ->one();
        if ($one) {
            return $one;
        }
        return $slug;
    }

    public static function getSlugByString($slug = ''){
        $_slug = new Slugs();
        $one = $_slug->select()
            ->from(['S' => Slugs::tableName()])
            ->where(ELOQUENT_EQUAL, ['S.slug' => $slug])
            ->one();
        if ($one) {
            return $one;
        }
        return $_slug;
    }
}