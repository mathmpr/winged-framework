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
     * @param $file bool | string
     *
     * Slugs constructor.
     */
    public function __construct($file = false)
    {
        parent::__construct();
        if ($file) {
            $this->fileObject = new File($file, false);
            if ($this->fileObject->exists()) {
                if (is_int(stripos($this->fileObject->getMimeType(), 'image'))) {
                    $this->fileObject = new Image($this->fileObject->file_path, false);
                }
            }
            $this->extension = $this->fileObject->getExtension();
        }
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
            },
            'centralizar_legenda' => function () {
                if (!$this->loaded('centralizar_legenda')) {
                    $this->centralizar_legenda = 0;
                }
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
     * @param $alt   string
     * @param $width int | bool
     *
     * @return false|string
     */
    public function getAsHtmlAdmin($alt = '', $width = false)
    {
        if (!$alt) {
            $alt = '';
        }
        if ($this->isVideo()) {
            ob_start();
            ?>
            <div data-midia-id="<?= $this->primaryKey() ?>" style="width: <?= !$width ? 150 : $width ?>px;"
                 class="father-video-element">
                <div class="video-container-element">
                    <video muted controls>
                        <source src="<?= $this->getFileUrl() ?>" type="<?= $this->getFile()->getMimeType() ?>">
                    </video>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            AdminAssets::compactHtml($content);
            return $content;
        } else if ($this->isSound()) {
            ob_start();
            ?>
            <div data-midia-id="<?= $this->primaryKey() ?>" style="width: <?= !$width ? 150 : $width ?>px;"
                 class="father-audio-element">
                <div class="audio-container-element">
                    <audio muted controls>
                        <source src="<?= $this->getFileUrl() ?>" type="<?= $this->getFile()->getMimeType() ?>">
                    </audio>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            AdminAssets::compactHtml($content);
            return $content;
        } else if ($this->isImage()) {
            ob_start();
            ?>
            <div data-midia-id="<?= $this->primaryKey() ?>" style="width: <?= !$width ? 150 : $width ?>px;"
                 class="father-image-element">
                <div class="image-container-element">
                    <img alt="<?= $this->alt_attr == '' ? $alt : $this->alt_attr ?>"
                         src="<?= $this->getFileUrl() ?>"
                         type="<?= $this->getFile()->getMimeType() ?>">
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            AdminAssets::compactHtml($content);
            return $content;
        } else {
            return $this->getFileUrl();
        }
    }

    /**
     * @param $alt   string
     *
     * @return false|string
     */
    public function getAsHtml($alt = '')
    {
        if (!$alt) {
            $alt = '';
        }
        if ($this->isVideo()) {
            ob_start();
            ?>
            <div data-midia-id="<?= $this->primaryKey() ?>" style="<?= $this->style_attr ?>"
                 class="father-video-element <?= $this->father_classes ?>"
                 id="<?= $this->father_id ?>">
                <div class="video-container-element">
                    <video id="<?= $this->element_id ?>" class="<?= $this->element_classes ?>" muted controls>
                        <source src="<?= $this->getFileUrl() ?>" type="<?= $this->getFile()->getMimeType() ?>">
                    </video>
                </div>
                <div class="video-legend-element <?= $this->centralizar_legenda == '1' ? 'centralized' : '' ?>">
                    <p><?= $this->legenda ?></p>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            AdminAssets::compactHtml($content);
            return $content;
        } else if ($this->isSound()) {
            ob_start();
            ?>
            <div data-midia-id="<?= $this->primaryKey() ?>" style="<?= $this->style_attr ?>"
                 class="father-audio-element <?= $this->father_classes ?>"
                 id="<?= $this->father_id ?>">
                <div class="audio-container-element">
                    <audio id="<?= $this->element_id ?>" class="<?= $this->element_classes ?>" muted controls>
                        <source src="<?= $this->getFileUrl() ?>" type="<?= $this->getFile()->getMimeType() ?>">
                    </audio>
                </div>
                <div class="audio-legend-element <?= $this->centralizar_legenda == '1' ? 'centralized' : '' ?>">
                    <p><?= $this->legenda ?></p>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            AdminAssets::compactHtml($content);
            return $content;
        } else if ($this->isImage()) {
            ob_start();
            ?>
            <div data-midia-id="<?= $this->primaryKey() ?>" style="<?= $this->style_attr ?>"
                 class="father-image-element <?= $this->getFile()->width() > $this->getFile()->height() ? 'height-auto' : 'width-auto' ?> <?= $this->father_classes ?>"
                 id="<?= $this->father_id ?>">
                <div class="image-container-element">
                    <?php
                    if ($this->href_attr != '') {
                        ?>
                        <a href="<?= $this->href_attr ?>" target="_blank">
                            <img id="<?= $this->element_id ?>" class="<?= $this->element_classes ?>"
                                 alt="<?= $this->alt_attr == '' ? $alt : $this->alt_attr ?>"
                                 src="<?= $this->getFileUrl() ?>"
                                 type="<?= $this->getFile()->getMimeType() ?>">
                        </a>
                        <?php
                    } else {
                        ?>
                        <img id="<?= $this->element_id ?>" class="<?= $this->element_classes ?>"
                             alt="<?= $this->alt_attr == '' ? $alt : $this->alt_attr ?>"
                             src="<?= $this->getFileUrl() ?>"
                             type="<?= $this->getFile()->getMimeType() ?>">
                        <?php
                    }
                    ?>
                </div>
                <div class="image-legend-element <?= $this->centralizar_legenda == '1' ? 'centralized' : '' ?>">
                    <p><?= $this->legenda ?></p>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            AdminAssets::compactHtml($content);
            return $content;
        } else {
            return $this->getFileUrl();
        }
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
            if (!$this->fileObject) {
                $this->fileObject = new File('', false);
            }
        }
        return $this->fileObject;
    }

    /**
     * @return string | bool
     */
    public function getFilePath()
    {
        if ($this->fileExists()) {
            return $this->fileObject->file_path;
        }
        return false;
    }

    /**
     * @return string | bool
     */
    public function getFileUrl()
    {
        if ($this->fileExists()) {
            return Winged::$protocol . $this->fileObject->file_path;
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
    public function getMidiaAsHtml()
    {
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

    /**
     * @param int $id
     *
     * @return bool|Midias
     */
    public static function getMidiaById($id = 0)
    {
        if (is_scalar($id)) {
            $id = intval($id);
            if (is_int($id) && $id > 0) {
                $midia = new Midias();
                $midia->autoLoadDb($id);
                return $midia;
            }
        }
        return false;
    }

    public static function gemDefault()
    {

    }

}