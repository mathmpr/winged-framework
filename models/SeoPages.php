<?php

class SeoPages extends Model
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    public $folder = './uploads/ogimage/';

    /** @var $id_seo integer */
    public $id_seo;

    /** @var $page_title string */
    public $page_title;

    /** @var $description string */
    public $description;

    /** @var $fb_title string */
    public $fb_title;

    /** @var $fb_description string */
    public $fb_description;

    /** @var $canonical_url string */
    public $canonical_url;

    /** @var $fb_image string */
    public $fb_image;

    /** @var $slug string */
    public $slug;

    /** @var $keywords string */
    public $keywords;

    public static function tableName()
    {
        return "seo_pages";
    }

    public static function primaryKeyName()
    {
        return "id_seo";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_seo = $pk;
            return $this;
        }
        return $this->id_seo;
    }

    public function behaviors()
    {
        return [
            'page_title' => function(){
                if($this->slug == ''){
                    $this->slug = $this->page_title;
                }
            },
            'slug' => function () {
                return CoreString::toUrl($this->slug);
            },
            'fb_image' => function () {
                return (new UploadAbstract())->process_posted_image($this, 'fb_image', CoreString::toUrl($this->page_title));
            },
        ];
    }

    public function reverseBehaviors()
    {
        return [];
    }

    public function rules()
    {
        return [
            'page_title' => [
                'required' => true,
                'length' => function(){
                    return CoreValidator::lengthSmallerOrEqual($this->page_title, 154);
                }
            ],
            'description' => [
                'required' => true,
                'length' => function(){
                    return CoreValidator::lengthSmallerOrEqual($this->description, 157);
                }
            ],
            'fb_title' => [
                'required' => true,
                'length' => function(){
                    return CoreValidator::lengthSmallerOrEqual($this->fb_title, 260);
                }
            ],
            'fb_description' => [
                'required' => true,
                'length' => function(){
                    return CoreValidator::lengthSmallerOrEqual($this->fb_description, 504);
                }
            ],
            'fb_image' => [
                'required' => true,
            ],
        ];
    }

    public function messages()
    {
        return [
            'page_title' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 150 foi excedido.'
            ],
            'description' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 153 foi excedido.'
            ],
            'fb_title' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 255 foi excedido.'
            ],
            'fb_description' => [
                'required' => 'Este campo é obrigatório.',
                'length' => 'O limite de caracteres de 500 foi excedido.'
            ],
            'fb_image' => [
                'required' => 'O envio de uma imagem para o Facebook é extremamente necessaria.'
            ],
        ];
    }

    public function labels()
    {
        return [
            'page_title' => 'Título H1 da página: ',
            'description' => 'Meta description: ',
            'fb_title' => 'Título do Facebook: ',
            'fb_description' => 'Descrição do Facebook: ',
            'canonical_url' => 'URL Canonica: ',
            'fb_image' => 'Imagem do Facebook: ',
            'slug' => 'URL da página: ',
            'keywords' => 'Palavra chave: '
        ];
    }

    public function getImagem($field = 'fb_image')
    {
        if (property_exists(get_class($this), $field)) {
            if ($this->{$field} != '') {
                if (file_exists($this->folder . $this->{$field})) {
                    if (WingedConfig::$USE_UNICID_ON_INCLUDE_ASSETS) {
                        return Winged::$protocol . $this->folder . $this->{$field} . '?cache=' . CoreToken::generate('sisisi', false, false);
                    } else {
                        return Winged::$protocol . $this->folder . $this->{$field};
                    }

                }
            }
        }
        return false;
    }

    public function getImagemPath($field = 'fb_image')
    {
        if (property_exists(get_class($this), $field)) {
            if ($this->{$field} != '') {
                if (file_exists($this->folder . $this->{$field})) {
                    return $this->folder . $this->{$field};
                }
            }
        }
        return false;
    }

}