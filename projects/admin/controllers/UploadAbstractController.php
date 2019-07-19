<?php

use Winged\Controller\Controller;
use Winged\Model\Login;

class UploadAbstractController extends Controller
{

    /**
     * @var $model null | UploadAbstract
     */
    public $model = null;

    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
        $this->model = new UploadAbstract();
    }

    public function actionUploadNormal()
    {
        if (!empty($_FILES)) {
            $thumb = false;
            if (isset($_POST['thumb'])) {
                $thumb = (int)$_POST['thumb'];
            }

            ini_set('upload_max_filesize', '64');

            $src = $_FILES["upload"]['tmp_name'];
            list($width, $height) = getimagesize($src);

            $folder = post('folder');

            $rwidth = post('width');

            $rheight = post('height');

            $img = $this->model->send_image($width, $height, $folder, $thumb, $_FILES['upload']);

            $img = $this->orientation($img['url']);

            if ($img['status']) {

                list($owidth, $oheight) = getimagesize($img['url']);

                if($oheight < $rheight){
                    $img = $this->resize($img['url'], false, $rheight);
                }
                if($owidth < $rwidth){
                    $img = $this->resize($img['url'], $rwidth);
                }

                if ($rheight > $rwidth) {
                    $img = $this->resize($img['url'], false, $rheight);
                } else {
                    $img = $this->resize($img['url'], $rwidth);
                }
                return $img;
            }
        }
    }

    public function actionNormalSet()
    {
        if (isset($_POST)) {
            return $this->model->crop_image($_POST);
        }
    }

    public function orientation($img)
    {

        $ext = explode('.', $img);

        $ext = strtolower(end($ext));

        if (in_array($ext, ['jpg', 'jpeg', 'tiff'])) {

            /*
            $errorCount = count(CoreError::$errors);

            try{
                $exif = @exif_read_data($img);
            }catch(Exception $e){
                $exif = [];
            }

            if (count(CoreError::$errors) > $errorCount) {
                array_pop(CoreError::$errors);
            }

            if (isset($exif['Orientation'])) {

                $image = imagecreatefromstring(file_get_contents($img));

                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;
                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                }

                $ext = explode('.', $img);
                $ext = end($ext);

                switch (strtolower($ext)) {
                    case 'gif':
                        imagegif($image, $img);
                        break;
                    case 'jpeg':
                        imagejpeg($image, $img, 100);
                        break;
                    case 'jpg':
                        imagejpeg($image, $img, 100);
                        break;
                    case 'png':
                        imagepng($image, $img, 1);
                        break;
                }

                list($nwidth, $nheight) = getimagesize($img);

                imagedestroy($image);

                return ['status' => true, 'width' => $nwidth, 'height' => $nheight, 'url' => $img];

            } else {*/

                list($nwidth, $nheight) = getimagesize($img);

                return ['status' => true, 'width' => $nwidth, 'height' => $nheight, 'url' => $img];
            //}

        }

        list($nwidth, $nheight) = getimagesize($img);

        return ['status' => true, 'width' => $nwidth, 'height' => $nheight, 'url' => $img];
    }

    public function resize($img = '', $width = false, $height = false)
    {

        $url = explode('/', $img);
        $name = array_pop($url);

        $ext = explode('.', $name);
        $ext = array_pop($ext);

        list($owidth, $oheight) = getimagesize($img);

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

        $new_image = imagecreatetruecolor($nwidth, $nheight);
        $origin = null;

        switch (strtolower($ext)) {
            case 'gif':
                $origin = imagecreatefromgif($img);
                imagecopyresampled($new_image, $origin, 0, 0, 0, 0, $nwidth, $nheight, $owidth, $oheight);
                imagegif($new_image, $img);
                break;

            case 'jpeg':
                $origin = imagecreatefromjpeg($img);
                imagecopyresampled($new_image, $origin, 0, 0, 0, 0, $nwidth, $nheight, $owidth, $oheight);
                imagejpeg($new_image, $img, 100);
                break;
            case 'jpg':
                $origin = imagecreatefromjpeg($img);
                imagecopyresampled($new_image, $origin, 0, 0, 0, 0, $nwidth, $nheight, $owidth, $oheight);
                imagejpeg($new_image, $img, 100);
                break;
            case 'png':
                imagesavealpha($new_image, true);
                $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
                imagefill($new_image, 0, 0, $color);
                $origin = imagecreatefrompng($img);
                imagecopyresampled($new_image, $origin, 0, 0, 0, 0, $nwidth, $nheight, $owidth, $oheight);
                imagepng($new_image, $img, 1);
                break;
        }

        imagedestroy($new_image);
        if ($origin) {
            imagedestroy($origin);
        }

        return ['status' => true, 'width' => $nwidth, 'height' => $nheight, 'url' => $img];
    }

}