<?php

namespace Winged\Image;

use Winged\File\File;
use Winged\Buffer\Buffer;

class Image extends File
{

    public $types = ['jpeg', 'jpg', 'gif', 'png'];

    public $mimes = ['image/png' => 'png', 'image/jpg' => 'jpg', 'image/jpeg' => 'jpg', 'image/gif' => 'gif'];
    /**
     * @var $original bool|resource
     */
    public $original = false;

    public function __construct($image, $forceCreate = true, $width = null, $height = null)
    {
        parent::__construct($image, $forceCreate);
        if ($this->exists()) {
            $this->create();
        } else {
            if ((!is_null($width) && !is_null($height)) && (numeric_is($width) && numeric_is($height))) {
                $this->createOriginal($width, $height);
            }
        }
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     * Case construct can't create a file in hard disk, the image are be created into memory and initialized for use other methods, how rotate, drawn and etc..
     */
    protected function createOriginal($width, $height)
    {
        $original = $this->createInMemory($this->getExtension(), $width, $height);
        $this->original = $original['image'];
        return $this;
    }

    /**
     * @param $extension
     * @param $width
     * @param $height
     * @return array
     * Create a image into memory
     */
    public function createInMemory($extension, $width, $height)
    {
        $image = null;
        $image = imagecreatetruecolor($width, $height);
        switch ($extension) {
            case 'png':
                $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
                imagefill($image, 0, 0, $color);
                imagesavealpha($image, true);
                imagealphablending($image, false);
                $black = imagecolorallocate($image, 0, 0, 0);
                imagecolortransparent($image, $black);
                break;
            case 'jpg':
                $color = imagecolorallocate($image, 255, 255, 255);
                imagefill($image, 0, 0, $color);
                break;
            case 'jpeg':
                $color = imagecolorallocate($image, 255, 255, 255);
                imagefill($image, 0, 0, $color);
                break;
            default:
                break;
        }
        return ['image' => $image, 'extension' => $extension, 'width' => $width, 'height' => $height];
    }

    /**
     * @param null $file
     * @return $this
     * Push image file into memory
     */
    public function create($file = null)
    {
        if (!$this->new) {
            switch ($this->getExtension()) {
                case 'png':
                    $this->original = imagecreatefrompng($this->file_path);
                    imagealphablending($this->original, false);
                    imagesavealpha($this->original, true);
                    $black = imagecolorallocate($this->original, 0, 0, 0);
                    imagecolortransparent($this->original, $black);
                    break;
                case 'jpeg':
                    $this->original = imagecreatefromjpeg($this->file_path);
                    break;
                case 'jpg':
                    $this->original = imagecreatefromjpeg($this->file_path);
                    break;
                case 'gif':
                    $this->original = imagecreatefromgif($this->file_path);
                    break;
                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * @param int $quality
     * @return $this
     * Save from memory into a file
     */
    public function save($quality = 100)
    {
        if ($this->original && !$this->exists()) {
            return $this;
        }
        if ($this->original) {
            switch ($this->getExtension()) {
                case 'png':
                    $quality = $quality - 10;
                    if ($quality < 10) {
                        $quality = 1;
                    } else {
                        $quality = (int)($quality / 9);
                    }
                    imagepng($this->original, $this->getWriteHandler(), $quality);
                    break;
                case 'jpeg':
                    if ($quality > 100) {
                        $quality = 100;
                    }
                    if ($quality < 0) {
                        $quality = 1;
                    }
                    imagejpeg($this->original, $this->getWriteHandler(), $quality);
                    break;
                case 'jpg':
                    if ($quality > 100) {
                        $quality = 100;
                    }
                    if ($quality < 0) {
                        $quality = 1;
                    }
                    imagejpeg($this->original, $this->getWriteHandler(), $quality);
                    break;
                case 'gif':
                    imagegif($this->original, $this->getWriteHandler());
                    break;
                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * @param int $quality
     * @return $this
     * Prints image to browser with the correct mime type.
     */
    public function printable($quality = 100)
    {
        if ($this->original) {
            switch ($this->getExtension()) {
                case 'png':
                    $quality = $quality - 10;
                    if ($quality < 10) {
                        $quality = 1;
                    } else {
                        $quality = (int)($quality / 9);
                    }
                    Buffer::kill();
                    header_remove();
                    header('Content-type: image/png');
                    imagepng($this->original, null, $quality);
                    exit;
                    break;
                case 'jpeg':
                    if ($quality > 100) {
                        $quality = 100;
                    }
                    if ($quality < 0) {
                        $quality = 1;
                    }
                    Buffer::kill();
                    header_remove();
                    header('Content-type: image/jpeg');
                    imagejpeg($this->original, null, $quality);
                    exit;
                    break;
                case 'jpg':
                    if ($quality > 100) {
                        $quality = 100;
                    }
                    if ($quality < 0) {
                        $quality = 1;
                    }
                    Buffer::kill();
                    header_remove();
                    header('Content-type: image/jpeg');
                    imagejpeg($this->original, null, $quality);
                    exit;
                    break;
                case 'gif':
                    Buffer::kill();
                    header_remove();
                    header('Content-type: image/gif');
                    imagegif($this->original, null);
                    exit;
                    break;
                default:
                    break;
            }
        }
        return $this;
    }

    protected function proportion($from, $to, $other)
    {
        $percent = $to * 100 / $from;
        $new_other = $other * $percent / 100;
        return $new_other;
    }

    public function width()
    {
        if ($this->original) {
            return imagesx($this->original);
        }
        return false;
    }

    public function height()
    {
        if ($this->original) {
            return imagesy($this->original);
        }
        return false;
    }

    public function exactlyResize($width = false, $height = false)
    {
        if ($this->original) {
            $owidth = $this->width();
            $oheight = $this->height();

            $nwidth = $owidth;
            $nheight = $oheight;

            if ($width) {
                if ($owidth < $width) {
                    $ww = $width - $owidth;
                    $percent = $ww * 100 / $owidth;
                    $nh = $oheight * $percent / 100;
                    $nwidth = $owidth + $ww;
                    $nheight = $oheight + $nh;
                }

                if ($owidth > $width) {
                    $ww = $owidth - $width;
                    $percent = $ww * 100 / $owidth;
                    $nh = $oheight * $percent / 100;
                    $nwidth = $owidth - $ww;
                    $nheight = $oheight - $nh;
                }
            }

            if ($height) {
                if ($oheight < $height) {
                    $ww = $height - $oheight;
                    $percent = $ww * 100 / $oheight;
                    $nh = $owidth * $percent / 100;
                    $nheight = $oheight + $ww;
                    $nwidth = $owidth + $nh;
                }

                if ($oheight > $height) {
                    $ww = $oheight - $height;
                    $percent = $ww * 100 / $oheight;
                    $nh = $owidth * $percent / 100;
                    $nheight = $oheight - $ww;
                    $nwidth = $owidth - $nh;
                }
            }
            $handles = $this->createInMemory($this->getExtension(), $nwidth, $nheight);
            imagecopyresampled($handles['image'], $this->original, 0, 0, 0, 0, $nwidth, $nheight, $owidth, $oheight);
            $this->original = $handles['image'];
            return $this->save();
        }
    }


    public function resize($divider = 2)
    {
        if ((is_float($divider) || is_int($divider))) {
            if ($divider < 1) {
                $divider += 1;
                if ($divider == 1) {
                    return $this;
                }
            }
            if ($this->original) {
                $width = ceil($this->width() / $divider);
                $height = ceil($this->height() / $divider);
                $handles = $this->createInMemory($this->getExtension(), $width, $height);
                imagecopyresampled($handles['image'], $this->original, 0, 0, 0, 0, $width, $height, $this->width(), $this->height());
                $this->original = $handles['image'];
                return $this->save();
            }
        }
        return $this;
    }

    /**
     * @param $fromX :begin cut from X of original image
     * @param $fromY :begin cut from Y of original image
     * @param int $width :size of the cut from point X
     * @param int $height :size of the cut from point Y
     * @return $this
     */
    public function cut($fromX, $fromY, $width = 0, $height = 0)
    {
        if ($this->original) {
            if ($fromX + $width > $this->width()) {
                $width = $this->width() - $fromX;
            }
            if ($fromY + $height > $this->height()) {
                $height = $this->height() - $fromY;
            }
            if ($fromX > $this->width()) {
                $fromX = 0;
            }
            if ($fromY > $this->height()) {
                $fromY = 0;
            }
            $image = $this->createInMemory($this->getExtension(), $width, $height);
            imagealphablending($image['image'], false);
            imagecolortransparent($image['image'], imagecolorallocate($image['image'], 0, 0, 0));
            imagecopyresampled($image['image'], $this->original, 0, 0, $fromX, $fromY, $width, $height, $width, $height);
            $this->original = $image['image'];
            return $this->save();
        }
        return $this;
    }

    /**
     * @param Image $image :image to merge
     * @param int $appendX :origin X in $this image for append target image
     * @param int $appendY :origin Y in $this image for append target image
     * @return $this|Image
     * All width & height of $this image go to final image and all width & height of target image so
     */
    public function merge(Image $image, $appendX = 0, $appendY = 0, $return = false)
    {
        if (is_object($image)) {
            if (get_class($image) === 'Image') {
                if ($image->original && $this->original) {
                    if ($appendX >= $this->width()) {
                        $appendX = $this->width();
                    }
                    if ($appendY >= $this->height()) {
                        $appendY = $this->height();
                    }
                    if (!is_int($appendX)) {
                        $appendX = $this->width();
                    }
                    if (!is_int($appendY)) {
                        $appendY = $this->width();
                    }

                    $width_from = 'origin';
                    $height_from = 'origin';

                    if ($image->width() > $this->width()) {
                        $min_width = $image->width();
                        $width_from = 'target';
                    } else {
                        $min_width = $this->width();
                    }

                    if ($image->height() > $this->height()) {
                        $min_height = $image->height();
                        $height_from = 'target';
                    } else {
                        $min_height = $this->height();
                    }

                    $result_width = $this->width() + $image->width() - ($this->width() - $appendX);

                    $result_height = $this->height() + $image->height() - ($this->height() - $appendY);

                    if ($result_height < $min_height) {
                        $result_height = $min_height;
                    }

                    if ($result_width < $min_width) {
                        $result_width = $min_width;
                    }

                    $new_image = $this->createInMemory($this->getExtension(), $result_width, $result_height);
                    imagecopy($new_image['image'], $this->original, 0, 0, 0, 0, $this->width(), $this->height());
                    imagecopy($new_image['image'], $image->original, $appendX, $appendY, 0, 0, $image->width(), $image->height());
                    if ($return) {
                        $this->original = $new_image['image'];
                        return $this;
                    }
                    $image_obj = (new Image($this->folder->folder . 'merged.' . $this->getExtension()));
                    $image_obj->original = $new_image['image'];
                    return $image_obj->save();
                }
            }
        }
        return $this;
    }

    /**
     * @param $angle :from 0 to 365
     * @return $this
     */
    public function rotate($angle)
    {
        if ($this->original) {
            $rotation = imagerotate($this->original, -$angle, imagecolorallocatealpha($this->original, 0, 0, 0, 127));
            imagealphablending($rotation, false);
            imagesavealpha($rotation, true);
            $this->original = $rotation;
            $this->save();
        }
        return $this;
    }

    public function toGif($quality = 100)
    {
        return $this->convert('gif', $quality);
    }

    public function toJpg($quality = 100)
    {
        return $this->convert('jpg', $quality);
    }

    public function toPng($quality = 100)
    {
        return $this->convert('png', $quality);
    }

    public function convert($ext = 'jpg', $quality = 100)
    {
        if ($this->original) {
            $image = $this->createInMemory($ext, $this->width(), $this->height());
            imagecopy($image['image'], $this->original, 0, 0, 0, 0, $this->width(), $this->height());
            $image_obj = (new Image($this->folder->folder . $this->getName() . '.' . $ext));
            $image_obj->original = $image['image'];
            $this->delete();
            return $image_obj->save($quality);
        }
    }

    public function drawn($text = '', $color = '#000', $alpha = 0, $inX = 0, $inY = 0, $font_size = 12, $angle = 0, $ttf_file = '')
    {
        if ($this->original) {
            if (begstr($color) != '#') {
                $color = '#' . $color;
            }
            if (strlen($color) === 4) {
                list($r, $g, $b) = sscanf($color, "#%01x%01x%01x");
            } else if (strlen($color) === 7) {
                list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
            } else {
                $color = '#000000';
                list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
            }
            $color = imagecolorallocatealpha($this->original, $r, $g, $b, $alpha);
            $file = new File($ttf_file, false);
            if ($file->exists()) {
                imagettftext($this->original, $font_size, $angle, $inX, $inY, $color, $file->file_path, $text);
            } else {
                imagettftext($this->original, $font_size, $angle, $inX, $inY, $color, (new File('./winged/file/assets/OpenSansRegular.ttf', false))->file_path, $text);
            }
            return $this->save();
        }
    }

    public function customSizePrint($width = false, $height = false)
    {
        if ($this->original) {
            $memory = new Image('temp.' . $this->getExtension(), false, $this->width(), $this->height());
            $memory->merge($this, 0, 0, true);
            if ($width && $height && (numeric_is($width) && numeric_is($height))) {
                $memory->exactlyResize($width, false);
                $memory->exactlyResize(false, $height);
            } else if ($width && numeric_is($width)) {
                $memory->exactlyResize($width, false);
            } else if ($height && numeric_is($height)) {
                $memory->exactlyResize(false, $height);
            }
            $memory->printable();
        }
    }

    /**
     * Copies the file to the specified location and returns a new File object with the path of that copied file.
     * If it fails in this process, the return of the method is the very object that made the call.
     * @param string $to
     * @return Image
     */
    public function copy($to = '')
    {
        $folder = null;
        if ($this->file_path != null && is_string($to)) {
            $file = explode('/', $this->file_path);
            $file = end($file);
            $folder = new CoreDirectory($to);
            if ($folder->exists()) {
                $to = $folder->folder . $file;
                copy($this->file_path, $to);
                return new Image($to);
            }
        }
        return $this;
    }

}