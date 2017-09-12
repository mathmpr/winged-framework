<?php

class CoreImage
{

    public $types = ['jpeg', 'jpg', 'gif', 'png'];
    public $mimes = ['image/png' => 'png', 'image/jpg' => 'jpg', 'image/jpeg' => 'jpg', 'image/gif' => 'gif'];

    public function __construct()
    {

    }


    public function createFromFormat($path, $width, $height, $format = 'jpg')
    {
        $image = imagecreatetruecolor($width, $height);
        $format = strtolower($format);
        if ($format == 'png') {
            imagesavealpha($image, true);
            $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $color);
            $orig = imagecreatefrompng($path);
        } else if ($format == 'jpeg' || $format == 'jpg') {
            $orig = imagecreatefromjpeg($path);
        } else if ($format == 'gif') {
            $orig = imagecreatefromgif($path);
        }
        return ['new' => $image, 'orig' => $orig];
    }

    public function saveImageWithFormat($path, $handles, $same_name = false)
    {
        if (file_exists($path) && !is_dir($path) && is_array($handles)) {
            $exploded_path = explode('.', $path);
            $type = array_pop($exploded_path);
            $file_name = array_pop($exploded_path);
            $new_path = $path;
            if (!$same_name) {
                $file_name .= uniqid() . '.' . $type;
                $new_path = implode('/', $exploded_path);
                $new_path .= $file_name;
                $new_path = './' . $new_path;
            }
            if ($type == 'png') {
                imagepng($handles['new'], $new_path);
            } else if ($type == 'jpeg' || $type == 'jpg') {
                imagejpeg($handles['new'], $new_path, 100);
            } else if ($type == 'gif') {
                imagegif($handles['new'], $new_path);
            }
            return ['create' => $file_name, 'path' => $new_path, 'type' => $type, 'mime' => get_key_by_value($type, $this->mimes)];
        }
        return false;
    }

    public function rename($path, $add = '', $type = 'prepend')
    {
        if (file_exists($path) && !is_dir($path)) {
            $exp = explode('/', $path);
            $name = array_pop($exp);
            if ($type == 'prepend') {
                $name = $add . $name;
            } else if ($type == 'append') {
                $exp_name = explode('.', $name);
                $name = $exp_name[0] . $add . '.' . $exp_name[1];
            } else {
                $exp_name = explode('.', $name);
                $name = $add . '.' . $exp_name[1];
            }
            $new_path = implode('/', $exp) . '/' . $name;
            if (rename($path, $new_path)) {
                return $new_path;
            }
        }
        return false;
    }

    public function verefyPath($path, $add = '', $type = 'prepend')
    {
        if (file_exists($path) && !is_dir($path)) {
            $exp = explode('/', $path);
            $name = array_pop($exp);
            if ($type == 'prepend') {
                $name = $add . $name;
            } else if ($type == 'append') {
                $exp_name = explode('.', $name);
                $name = $exp_name[0] . $add . '.' . $exp_name[1];
            } else {
                $exp_name = explode('.', $name);
                $name = $add . '.' . $exp_name[1];
            }
            $new_path = implode('/', $exp) . '/' . $name;
            return $new_path;
        }
        return false;
    }

    public function getExtension($path)
    {
        $exp = explode('.', $path);
        return end($exp);
    }

    public function getSizeProperty($path)
    {
        if (file_exists($path) && !is_dir($path)) {
            $img_prop = getimagesize($path);
            return ['width' => $img_prop[0], 'height' => $img_prop[1]];
        }
        return ['width' => -1, 'height' => -1];
    }

    public function getProportion($from, $to, $other)
    {
        $percent = $to * 100 / $from;
        $new_other = $other * $percent / 100;
        return $new_other;
    }

    public function exactlyResize($path, $width = 0, $height = 0, $same_name = false)
    {
        if ($width > 0 || $height > 0) {
            if (file_exists($path) && !is_dir($path)) {
                $img_prop = null;
                try {
                    $img_prop = getimagesize($path);
                } catch (Exception $e) {
                    return false;
                }

                $owidth = $img_prop[0];
                $oheight = $img_prop[1];

                $new_width = $width;
                $new_height = $height;

                if (($width == 0 || $width == '') && $height > 0 && $height != '') {
                    $new_width = $this->getProportion($oheight, $height, $owidth);
                } else if (($height == 0 || $height == '') && $width > 0 && $width != '') {
                    $new_height = $this->getProportion($owidth, $width, $oheight);
                }

                $exploded_path = explode('.', $path);
                $type = end($exploded_path);

                if (is_array($img_prop)) {

                    $width = $img_prop[0];
                    $height = $img_prop[1];

                    $handles = $this->createFromFormat($path, $new_width, $new_height, $type);

                    imagecopyresampled($handles['new'], $handles['orig'], 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                    return $this->saveImageWithFormat($path, $handles, $same_name);
                }

            }
        }
    }

    public function resize($path = '', $divider = 2, $same_name = false)
    {
        if ((is_float($divider) || is_int($divider))) {
            if ($divider < 1) {
                $divider += 1;
                if ($divider == 1) {
                    return false;
                }
            }
            if (file_exists($path) && !is_dir($path)) {
                $exploded_path = explode('.', $path);
                $type = end($exploded_path);
                $img_prop = null;
                try {
                    $img_prop = getimagesize($path);
                } catch (Exception $e) {
                    return false;
                }
                if (is_array($img_prop)) {

                    $width = $img_prop[0];
                    $height = $img_prop[1];

                    $new_width = ceil($width / $divider);
                    $new_height = ceil($height / $divider);

                    $handles = $this->createFromFormat($path, $new_width, $new_height, $type);

                    imagecopyresampled($handles['new'], $handles['orig'], 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                    return $this->saveImageWithFormat($path, $handles, $same_name);
                }
            }
        }
        return false;
    }

    protected function cutOut($cropped_rotated_image, $imgX1, $imgY1, $cropW, $cropH)
    {
        $final_image = imagecreatetruecolor($cropW, $cropH);
        imagesavealpha($final_image, true);
        $color = imagecolorallocatealpha($final_image, 0, 0, 0, 127);
        imagefill($final_image, 0, 0, $color);
        imagealphablending($final_image, false);
        imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
        imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);
        return $final_image;
    }

    public function rotate($resizedImage, $angle, $imgH, $imgW)
    {
        $rotated_image = imagerotate($resizedImage, -$angle, 0);
        $rotated_width = imagesx($rotated_image);
        $rotated_height = imagesy($rotated_image);
        $dx = $rotated_width - $imgW;
        $dy = $rotated_height - $imgH;
        $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
        imagesavealpha($cropped_rotated_image, true);
        $color = imagecolorallocatealpha($cropped_rotated_image, 0, 0, 0, 127);
        imagefill($cropped_rotated_image, 0, 0, $color);
        imagealphablending($cropped_rotated_image, false);
        imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
        imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
        return $cropped_rotated_image;
    }

    public function cut($imgUrl, $imgInitW, $imgInitH, $imgW, $imgH, $imgY1, $imgX1, $cropW, $cropH, $angle, $same_name = false)
    {

        $what = getimagesize($imgUrl);
        $resizedImage = imagecreatetruecolor($imgW, $imgH);
        switch (strtolower($what['mime'])) {
            case 'image/png':
                imagesavealpha($resizedImage, true);
                $color = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefill($resizedImage, 0, 0, $color);
                imagealphablending($resizedImage, false);
                $source_image = imagecreatefrompng($imgUrl);
                $type = "png";
                break;
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($imgUrl);
                $type = "jpeg";
                break;
            case 'image/gif':
                $source_image = imagecreatefromgif($imgUrl);
                $type = "gif";
                break;
            default:
                die('formato de imagem inavalido');
        }
        imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
        $cropped_rotated_image = $this->rotate($resizedImage, $angle, $imgH, $imgW);
        $final = $this->cutOut($cropped_rotated_image, $imgX1, $imgY1, $cropW, $cropH);

        return $this->saveImageWithFormat($imgUrl, ['new' => $final], $same_name);

    }

}