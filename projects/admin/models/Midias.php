<?php

use Winged\Model\Model;
use Winged\File\File;
use Winged\Image\Image;
use Winged\Winged;

/**
 * Class Midias
 */
class Midias extends Model
{

    const DEFAULT_FOLDER = './uploads/';

    /**
     * @var null | File | Image
     */
    private $fileObject = null;

    public $id = 0;

    public $file_name;

    public $href_attr;

    public $file_type;

    public $extension;

    public $entry_date;

    public $father_classes;

    public $element_classes;

    public $father_id;

    public $element_id;

    public $alt_attr;

    public $centralizar_legenda;

    public $legenda;

    public $style_attr;

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
        return 'midias';
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
            'file_name' => function () {
                $this->getFile();
            }
        ];
    }

    /**
     * @return array
     */
    public function reverseBehaviors()
    {
        return [
            'file_name' => function () {
                $this->getFile();
            }
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [

        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [

        ];
    }

    /**
     * @return array
     */
    public function labels()
    {
        return [
            'father_id' => 'Atributo ID para o elemento pai: ',
            'element_id' => 'Atributo ID para o elemento principal: ',
            'father_classes' => 'Classes CSS para o elemento pai: ',
            'element_classes' => 'Classes CSS para o elemento principal: ',
            'style_attr' => 'Folha de estilo para o elemento pai: ',
            'alt_attr' => 'Descrição da mídia: ',
            'legenda' => 'Legenda da mídia: ',
            'href_attr' => 'Para onde a mídia pode levar (URL): ',
            'centralizar_legenda' => 'Centralizar legenda: '
        ];
    }

    /**
     * @return Image | File | null
     */
    public function getFile()
    {
        if ($this->file_name && $this->extension) {
            if ($this->isImage()) {
                $this->fileObject = new Image(Midias::DEFAULT_FOLDER . $this->file_name, false);
            } else {
                $this->fileObject = new File(Midias::DEFAULT_FOLDER . $this->file_name, false);
            }
        } else {
            $this->fileObject = new File('', false);
        }
        return $this->fileObject;
    }

    /**
     * @return string | bool
     */
    public function getFilePath()
    {
        if ($this->fileExists()) {
            return Midias::DEFAULT_FOLDER . $this->fileObject->file;
        }
        return false;
    }

    /**
     * @return string | bool
     */
    public function getFileUrl()
    {
        if ($this->fileExists()) {
            return Winged::$protocol . Midias::DEFAULT_FOLDER . $this->fileObject->file;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getType()
    {
        if ($this->extension) {
            return Midias::getFileType($this->extension);
        }
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->getType() === 'image';
    }

    /**
     * @return bool
     */
    public function isVideo()
    {
        return $this->getType() === 'video';
    }

    /**
     * @return bool
     */
    public function isZip()
    {
        return $this->getType() === 'zip';
    }

    /**
     * @return bool
     */
    public function isSound()
    {
        return $this->getType() === 'sound';
    }

    /**
     * @return bool
     */
    public function isXls()
    {
        return $this->getType() === 'xls';
    }

    /**
     * @return bool
     */
    public function isPdf()
    {
        return $this->getType() === 'pdf';
    }

    /**
     * @return bool
     */
    public function isDoc()
    {
        return $this->getType() === 'doc';
    }

    /**
     * @return $string
     */
    public function getMidiaAsHtml(){
        return '';
    }

    /**
     * @return $this|bool
     */
    private function fileExists()
    {
        if (is_object($this->fileObject)) {
            if ($this->fileObject->exists()) {
                return $this;
            }
        }
        return false;
    }

    /**
     * @param $extension
     *
     * @return string
     */
    public static function getFileType($extension)
    {
        if (in_array($extension, ['doc', 'docx'])) return 'doc';
        if (in_array($extension, ['pdf'])) return 'pdf';
        if (in_array($extension, ['xls', 'xlsx', 'csv'])) return 'xls';
        if (in_array($extension, ['jpg', 'jpeg', 'tiff', 'png', 'gif'])) return 'image';
        if (in_array($extension, ['mpeg', 'avi', 'mp4', 'ogg'])) return 'video';
        if (in_array($extension, ['mp3'])) return 'sound';
        if (in_array($extension, ['zip', 'rar'])) return 'zip';
        return 'other';
    }

}