<?php

use Winged\Utils\RandomName;

/**
 * ImageProcessor - Faz uplod, redimensiona, renomeia, e recorta imagens
 *
 * @package TrixeSystem
 * @since 0.1
 */
class ImageProcessor
{
    /**
     * $width
     *
     * largura inicial da imagem
     *
     * @access public
     */
    public $width;

    /**
     * $height
     *
     * altura inicial da imagem
     *
     * @access public
     */
    public $height;

    /**
     * $novo_width
     *
     * largura desejada no recorte
     *
     * @access public
     */
    public $novo_width;

    /**
     * $novo_height
     *
     * altura desejada no recorte
     *
     * @access public
     */
    public $novo_height;

    /**
     * $pasta
     *
     * pasta em que devera ser salva imagem
     *
     * @access public
     */
    public $pasta;

    /**
     * $tipos
     *
     * extensoes de imagens aceitas pela classe
     *
     * @access protected
     */
    protected $tipos = array("jpeg", "png", "gif", "jpg");

    /**
     * $resize_image
     *
     * redimensiona a imagem // redimensionar()
     *
     * @access public
     */
    public function resize_image($caminho, $nomearquivo)
    {
        $width = $this->width;
        $height = $this->height;

        list($width_orig, $height_orig, $tipo, $atributo) = getimagesize($caminho . $nomearquivo);

        if ($width_orig >= $height_orig) {
            $height = ($width / $width_orig) * $height_orig;
        } elseif ($width_orig < $height_orig) {
            $width = ($height / $height_orig) * $width_orig;
        }

        $this->novo_width = $width;
        $this->novo_height = $height;

        $novaimagem = imagecreatetruecolor($width, $height);

        switch ($tipo) {
            case 1:
                $origem = imagecreatefromgif($caminho . $nomearquivo);
                imagecopyresampled($novaimagem, $origem, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                imagegif($novaimagem, $caminho . $nomearquivo);
                break;

            case 2:
                $origem = imagecreatefromjpeg($caminho . $nomearquivo);
                imagecopyresampled($novaimagem, $origem, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                imagejpeg($novaimagem, $caminho . $nomearquivo);
                break;
            case 3:
                imagesavealpha($novaimagem, true);
                $color = imagecolorallocatealpha($novaimagem, 0, 0, 0, 127);
                imagefill($novaimagem, 0, 0, $color);
                $origem = imagecreatefrompng($caminho . $nomearquivo);
                imagecopyresampled($novaimagem, $origem, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                imagepng($novaimagem, $caminho . $nomearquivo);
                break;
        }

        imagedestroy($novaimagem);
        imagedestroy($origem);

        return true;
    }

    /**
     * $clear_name_img
     *
     * remove carateres especiais, espacos e acentos do nome da imagem // remove_acento()
     *
     * @access protected
     */
    protected function clear_name_img($texto)
    {
        $com_acento = ['à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú', 'Ÿ',];
        $sem_acento = ['a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', '0', 'U', 'U', 'U', 'Y',];
        $final = str_replace($com_acento, $sem_acento, $texto);
        $com_pontuacao = ['´', '`', '¨', '^', '~', ' ', '-', '(', ')'];
        $sem_pontuacao = ['', '', '', '', '', '_', '_', '_', '_'];
        $final = str_replace($com_pontuacao, $sem_pontuacao, $final);
        return $final;
    }

    /**
     * $save_image
     *
     * salva a imagem pre tratada // salvar()
     *
     * @access public
     */
    public function save_image($caminho, $file, $thumb)
    {
        $file['name'] = uniqid() . $file['name'];

        $file['name'] = $this->clear_name_img($file['name']);

        $uploadfile = $caminho . $file['name'];

        $arr_type = explode('/', $file['type']);

        $type = array_pop($arr_type);

        $type = strtolower($type);

        if (array_search($type, $this->tipos) === false) {
            return ["status" => "error", "message" => "Envie apenas imagens no formato jpeg, png ou gif!"];
        } else if (!move_uploaded_file($file['tmp_name'], $uploadfile)) {
            switch ($file['error']) {
                case 1:
                    return ["status" => "error", "message" => "O tamanho do arquivo é maior que o tamanho permitido."];
                    break;
                case 2:
                    return ["status" => "error", "message" => "O tamanho do arquivo é maior que o tamanho permitido."];
                    break;
                case 3:
                    return ["status" => "error", "message" => "O upload do arquivo foi feito parcialmente."];
                    break;
                case 4:
                    return ["status" => "error", "message" => "Não foi feito o upload de arquivo."];
                    break;
            }
        } else {
            if ($thumb) {
                copy($uploadfile, $caminho . "thumb_" . $file['name']);
                $this->width = $thumb;
            }

            list($width_orig, $height_orig) = getimagesize($uploadfile);

            if ($width_orig > $this->width || $height_orig > $this->height) {
                $this->resize_image($caminho, $file['name']);
                if ($thumb) {
                    $this->resize_image($caminho, "thumb_" . $file['name']);
                }
            } else {
                $this->novo_width = $width_orig;
                $this->novo_height = $height_orig;
            }

            if ($thumb) {
                $file['name'] = "thumb_" . $file['name'];
            }

            return ["status" => "success", "url" => $this->pasta . $file['name'], "width" => $this->novo_width, "height" => $this->novo_height];
        }
        return;
    }

    /**
     * $turn_image
     *
     * faz a rotação da imagem caso necessario // girar()
     *
     * @access public
     */
    protected function turn_image($resizedImage, $angle, $imgH, $imgW)
    {
        $rotated_image = imagerotate($resizedImage, -$angle, 0);
        // pega a largura e altura da imagem angulada
        $rotated_width = imagesx($rotated_image);
        $rotated_height = imagesy($rotated_image);
        // diferença entre a angulada e a original
        $dx = $rotated_width - $imgW;
        $dy = $rotated_height - $imgH;
        // corta a imagem no angulo selecionado mantendo no quadro
        $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);

        imagesavealpha($cropped_rotated_image, true);
        $color = imagecolorallocatealpha($cropped_rotated_image, 0, 0, 0, 127);
        imagefill($cropped_rotated_image, 0, 0, $color);
        imagealphablending($cropped_rotated_image, false);

        imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
        imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);

        return $cropped_rotated_image;
    }

    /**
     * $recrop_image
     *
     * recorta a imagem para o tamanho e posição especuificada // recortar()
     *
     * @access protected
     */
    protected function recrop_image($cropped_rotated_image, $imgX1, $imgY1, $cropW, $cropH)
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

    /**
     * @param $img
     * @param $beggin_x
     * @param $beggin_y
     * @param $width
     * @param $height
     * @return array
     */
    public function crop_image($img, $beggin_x, $beggin_y, $width, $height)
    {
        if (!is_writable(dirname($img))) {
            $response = [
                "status" => false,
                "message" => 'file no not exists',
            ];
        } else {

            $local = explode('/', $img);
            array_pop($local);
            $local = implode('/', $local) . '/' . RandomName::generate('sisisisisi', false, false);

            $what = getimagesize($img);
            $dst_image = imagecreatetruecolor($width, $height);

            switch (strtolower($what['mime'])) {
                case 'image/png':
                    imagesavealpha($dst_image, true);
                    $color = imagecolorallocatealpha($dst_image, 0, 0, 0, 127);
                    imagefill($dst_image, 0, 0, $color);
                    imagealphablending($dst_image, false);
                    $src_image = imagecreatefrompng($img);
                    $type = "png";
                    break;
                case 'image/jpeg':
                    $src_image = imagecreatefromjpeg($img);
                    $type = "jpeg";
                    break;
                case 'image/gif':
                    $src_image = imagecreatefromgif($img);
                    $type = "gif";
                    break;
                default:
                    die('invalid format');
            }

            imagecopyresampled($dst_image, $src_image, 0, 0, $beggin_x, $beggin_y, $width, $height, $width, $height);

            if ($type == "png") {
                $local = $local . '.png';
                imagepng($dst_image, $img, 7);
                rename($img, $local);
            } else {
                $local = $local . '.jpg';
                imagejpeg($dst_image, $img, 80);
                rename($img, $local);
            }

            $response = [
                "status" => true,
                "url" => $local,
            ];
        }
        return $response;
    }
}

?>