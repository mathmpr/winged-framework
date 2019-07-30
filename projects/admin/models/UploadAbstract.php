<?php

use Winged\Utils\RandomName;
use Winged\Image\Image;

class UploadAbstract
{

    public function send_image($width, $height, $folder, $thumb, $file)
    {
        $upload = new ImageProcessor();
        $upload->width = $width;
        $upload->height = $height;
        $upload->pasta = $folder;
        $result = $upload->save_image($folder, $file, $thumb);
        return $result;
    }

    public function crop_image($image)
    {
        $img = array_key_exists_check('file', $image);

        $beggin_x = array_key_exists_check('x', $image);
        $beggin_y = array_key_exists_check('y', $image);

        $width = array_key_exists_check('w', $image);
        $height = array_key_exists_check('h', $image);

        $upload = new ImageProcessor();
        return $upload->crop_image($img, $beggin_x, $beggin_y, $width, $height);
    }

    public function process_posted_image(&$model, $field_name, $image_current_name, $add = '')
    {

        /**
         * @var $model \Winged\Model\Model
         * @var $clone \Winged\Model\Model
         * @var $model ->folder string
         */

        $clone = clone $model;
        $clone->autoLoadDb($model->primaryKey());

        if ($image_current_name === '') {
            $image_current_name = RandomName::generate('sisisisi', false, false);
        }

        if ($model->{$field_name} != '' && $model->{$field_name} != 'remove' && $model->{$field_name} != 'keep') {

            $currentImage = new Image($model->{$field_name}, false);
            $cloneImage = new Image($clone->folder . $clone->{$field_name}, false);
            $mobile = null;
            if ($currentImage->exists()) {
                $currentImage = $currentImage->toJpg(100);
                $currentImage->rename($image_current_name);
            }

            $model->onSaveSuccess('move_image', function ($currentImage, $cloneImage, $field_name, $mobile) {
                /**
                 * @var $currentImage Image
                 * @var $cloneImage Image
                 * @var $mobile Image
                 */
                $currentImage->crop($this->folder);
                if ($cloneImage->exists()) {
                    if ($cloneImage->getName() != $currentImage->getName()) {
                        $cloneImage->delete();
                    }
                }
                $mobile = $currentImage->copy('./uploads/buffer/');
                $mobile->rename($mobile->getName() . '_mobile');
                $mobile->crop($this->folder);
                $mobile->exactlyResize(400);
                $mobile->toJpg(85);
                $currentImage = $currentImage->toJpg(80);
                $this->{$field_name} = $currentImage->file;
            }, [$currentImage, $cloneImage, $field_name, $mobile]);

            $model->onSaveError('change_name', function ($currentImage, $field_name) {
                /**
                 * @var $currentImage Image
                 */
                $this->{$field_name} = $currentImage->file_path;
            }, [$currentImage, $field_name]);

            $model->onValidateError('change_name', function ($currentImage, $field_name) {
                /**
                 * @var $currentImage Image
                 */
                $this->{$field_name} = $currentImage->file_path;
            }, [$currentImage, $field_name]);


            return $currentImage->file;
        } else if ($model->{$field_name} === 'keep') {
            $clone = clone $model;
            $clone->autoLoadDb($model->primaryKey());
            $cloneImage = new Image($clone->folder . $clone->{$field_name}, false);
            if ($cloneImage->exists()) {
                if ($cloneImage->getName() != $image_current_name) {
                    $model->onSaveSuccess('move_image', function ($cloneImage, $image_current_name) {
                        /**
                         * @var $cloneImage Image
                         */
                        $cloneImage->rename($image_current_name);
                    }, [$cloneImage, $image_current_name]);
                }
            }
            return $image_current_name . '.' . $cloneImage->getExtension();
        } else if ($model->{$field_name} === 'remove') {
            $clone = clone $model;
            $clone->autoLoadDb($model->primaryKey());
            $image = new Image($model->folder . $clone->{$field_name}, false);
            if ($image->exists()) {
                $model->onSaveSuccess('really_remove', function ($image) {
                    /**
                     * @var $image Image
                     */
                    $image->delete();
                }, [$image]);
            }
            return '';
        }
        $model->unload($field_name);
        return ['null' => null];
    }

}