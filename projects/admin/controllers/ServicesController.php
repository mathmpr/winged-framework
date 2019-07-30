<?php

use Winged\Controller\Controller;
use Winged\Upload\Upload;
use Winged\File\File;
use Winged\Image\Image;
use Winged\Date\Date;

/**
 * Class ServicesController
 */
class ServicesController extends Controller
{
    public function __construct()
    {
        !Login::permission() ? $this->redirectTo() : null;
        parent::__construct();
    }

    /**
     * @return array
     */
    public function actionGetCidades()
    {
        $model = (new Cidades())
            ->select()
            ->from(['CIDADES' => 'cidades'])
            ->orderBy(ELOQUENT_ASC, 'CIDADES.cidade');
        if (postset('id_estado')) {
            $model->where(ELOQUENT_EQUAL, ['CIDADES.id_estado' => post('id_estado')]);
        }
        return ['status' => true, 'data' => array2htmlselect($model->execute(true), 'cidade', 'id_cidade')];
    }

    /**
     * @return array
     */
    public function actionGetBairros()
    {
        $model = (new Bairros())
            ->select()
            ->from(['BAIRROS' => 'bairros'])
            ->orderBy(ELOQUENT_ASC, 'BAIRROS.nome');
        if (postset('id_estado')) {
            $model->where(ELOQUENT_EQUAL, ['BAIRROS.id_estado' => post('id_estado')]);
            if (postset('id_cidade')) {
                $model->andWhere(ELOQUENT_EQUAL, ['BAIRROS.id_cidade' => post('id_cidade')]);
                return ['status' => true, 'data' => array2htmlselect($model->execute(true), 'nome', 'id_bairro')];
            }
        }
        return ['status' => false, 'data' => []];
    }

    /**
     * @return array
     */
    public function actionGetSubcategorias()
    {
        $model = (new ProdutosSubcategorias())
            ->select()
            ->from(['SUBCATEGORIAS' => 'produtos_subcategorias'])
            ->orderBy(ELOQUENT_ASC, 'SUBCATEGORIAS.nome');
        if (postset('id_categoria')) {
            $model->where(ELOQUENT_EQUAL, ['SUBCATEGORIAS.id_categoria' => post('id_categoria')]);
        }
        return ['status' => true, 'data' => array2htmlselect($model->execute(true), 'nome', 'id_subcategoria')];
    }

    /**
     * @return array
     */
    public function actionValidateSlug()
    {
        $slug = new Slugs();
        $slug->load([
            Slugs::tableName() => [
                'slug' => post('slug')
            ]
        ]);
        $slug->validate();
        if (!$slug->hasErrors()) {
            return ['status' => true];
        }

        $slugExists = Slugs::getSlug(post('linkTo'), post('tableName'))->slug;

        if (post('slug') === $slugExists) {
            return ['status' => true];
        }

        return ['status' => false, 'errors' => $slug->getErrors()];
    }


    /**
     * @return array
     */
    public function actionGetMidiaInformation()
    {
        $midias = (new Midias())
            ->select()
            ->from(['M' => Midias::tableName()])
            ->where(ELOQUENT_IN, ['M.' . Midias::primaryKeyName() => post('in')])
            ->execute();
        $response = [];
        if ($midias) {
            /**
             * @var $midias Midias[]
             */
            foreach ($midias as $midia) {
                $current = [
                    'id' => $midia->primaryKey(),
                    'html' => $midia->getAsHtml(),
                    'pure_url' => $midia->getFileUrl(),
                    'mime_type' => $midia->getFile()->getMimeType(),
                ];
                if ($midia->isImage()) {
                    $current['width'] = $midia->getFile()->width();
                    $current['height'] = $midia->getFile()->height();
                    $current['proportion'] = $midia->getFile()->width() . ' x ' . $midia->getFile()->height();
                }
                $current['type'] = $midia->getType();
                $current['size'] = number_format(floatval($midia->getFile()->filesize() / 1024 / 1024), 3, '.', '') . ' MB';
                $response[] = $current;
            }
            return ['status' => true, 'data' => $response];
        }
        return ['status' => false];
    }

    /**
     * @return array
     */
    public function actionGetEditFormMidia()
    {
        if (Login::permission() || Login::permissionAdm()) {
            $midia = new Midias();
            $midia->autoLoadDb(post('id'));
            if ($midia->primaryKey()) {
                if ($midia->isImage()) {
                    return ['status' => true, 'html' => $this->partial('_includes/midia.edit.form.image', ['midia' => $midia], true)];
                }
                if ($midia->isSound() || $midia->isVideo()) {
                    return ['status' => true, 'html' => $this->partial('_includes/midia.edit.form.video', ['midia' => $midia], true)];
                }
                return ['status' => true, 'html' => $this->partial('_includes/midia.edit.form.default', ['midia' => $midia], true)];
            }
        }
        return ['status' => false];
    }

    /**
     * @return array
     */
    public function actionUpdateMidia()
    {
        if (Login::permission() || Login::permissionAdm()) {
            return ['status' => (new Midias())->load($_POST)->save()];
        }
        return ['status' => false];
    }

    /**
     * @return array
     */
    public function actionDeleteMidia()
    {
        if (Login::permission() || Login::permissionAdm()) {
            return ['status' => (new Midias())->load([
                Midias::tableName() => [
                    'id' => post('id')
                ]
            ])->remove()
            ];
        }
        return ['status' => false];
    }

    /**
     * @return array
     */
    public function actionGetMidia()
    {
        $midias = (new Midias())
            ->select()
            ->from(['M' => Midias::tableName()])
            ->orderBy(ELOQUENT_DESC, 'M.entry_date');

        $countMidias = $midias->count();

        $midias = $midias->limit(post('from'), post('to'))
            ->execute();

        /**
         * @var $midias Midias[] | null
         */

        if ($midias) {
            $response = [];
            foreach ($midias as $midia) {
                ob_start();
                if ($midia->isImage()) {
                    ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div data-id="<?= $midia->primaryKey() ?>"
                             class="type icon image"
                             style="background-image: url(<?= $midia->getFileUrl() ?>);"></div>
                        <div class="file-name"><?= $midia->getFile()->getName() ?></div>
                        <div class="controls">
                            <ul>
                                <li><a data-id="<?= $midia->primaryKey() ?>" class="delete icon-trash"></a></li>
                                <li><a data-width="<?= $midia->getFile()->width() ?>"
                                       data-height="<?= $midia->getFile()->height() ?>"
                                       data-pure-url="<?= $midia->getFileUrl() ?>" data-id="<?= $midia->primaryKey() ?>"
                                       class="update-image icon-file-picture2"></a></li>
                                <li><a data-id="<?= $midia->primaryKey() ?>" class="update icon-quill4"></a></li>
                            </ul>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div data-id="<?= $midia->primaryKey() ?>"
                             class="type icon <?= $midia->getType() ?>">
                            <?php
                            if ($midia->isVideo()) {
                                ?>
                                <div class="overlay-content">
                                    <video autoplay muted loop>
                                        <source src="<?= $midia->getFileUrl() ?>"
                                                type="<?= $midia->getFile()->getMimeType() ?>">
                                    </video>
                                </div>
                                <?php
                            }
                            ?>
                            <?php
                            if ($midia->isSound()) {
                                ?>
                                <div class="overlay-content">
                                    <audio controls>
                                        <source src="<?= $midia->getFileUrl() ?>"
                                                type="<?= $midia->getFile()->getMimeType() ?>">
                                    </audio>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="file-name"><?= $midia->getFile()->getName() ?></div>
                        <div class="controls">
                            <ul>
                                <li><a data-id="<?= $midia->primaryKey() ?>" class="delete icon-trash"></a></li>
                                <li><a data-id="<?= $midia->primaryKey() ?>" class="update icon-quill4"></a></li>
                            </ul>
                        </div>
                    </div>
                    <?php
                }
                $current['html'] = ob_get_clean();
                $current['model'] = $midia;
                $response[] = $current;
            }
            return ['status' => true, 'data' => array_reverse($response), 'have_more' => (($countMidias - post('to')) > 0)];
        }
        return ['status' => false, 'data' => []];
    }

    /**
     * @return array
     */
    public function actionUpload()
    {
        if (Login::permission() || Login::permissionAdm()) {
            $uploader = new Upload();
            $uploader->setOptions(Midias::DEFAULT_FOLDER, 'img,doc,zip,audio,video', '', '', 256, 'preserve');
            $files = $uploader->uploadFile('file');
            $response = [];
            $status = false;
            if ($files) {
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $current = [];
                        if ($file['status']) {
                            $fileObject = new File($file['path'], false);
                            if ($fileObject->exists()) {
                                $midia = new Midias();
                                $midia->load([
                                    Midias::tableName() => [
                                        'file_name' => $fileObject->file,
                                        'file_type' => Midias::getFileType($fileObject->getExtension()),
                                        'extension' => $fileObject->getExtension(),
                                        'entry_date' => (new Date(time()))->sql(),
                                    ]
                                ]);

                                if (post('id')) {
                                    $midia->load([
                                        Midias::tableName() => [
                                            Midias::primaryKeyName() => post('id')
                                        ]
                                    ]);
                                    $midia->unload('entry_date');
                                }

                                if ($midia->save()) {
                                    $status = true;
                                    ob_start();
                                    if ($midia->isImage()) {
                                        ?>
                                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                                            <div data-id="<?= $midia->primaryKey() ?>"
                                                 class="type icon image"
                                                 style="background-image: url(<?= $midia->getFileUrl() ?>);"></div>
                                            <div class="file-name"><?= $midia->getFile()->getName() ?></div>
                                            <div class="controls">
                                                <ul>
                                                    <li><a data-id="<?= $midia->primaryKey() ?>"
                                                           class="delete icon-trash"></a></li>
                                                    <li><a data-width="<?= $midia->getFile()->width() ?>"
                                                           data-height="<?= $midia->getFile()->height() ?>"
                                                           data-pure-url="<?= $midia->getFileUrl() ?>"
                                                           data-id="<?= $midia->primaryKey() ?>"
                                                           class="update-image icon-file-picture2"></a></li>
                                                    <li><a data-id="<?= $midia->primaryKey() ?>"
                                                           class="update icon-quill4"></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                                            <div data-id="<?= $midia->primaryKey() ?>"
                                                 class="type icon <?= $midia->getType() ?>">
                                                <?php
                                                if ($midia->isVideo()) {
                                                    ?>
                                                    <div class="overlay-content">
                                                        <video autoplay muted loop>
                                                            <source src="<?= $midia->getFileUrl() ?>"
                                                                    type="<?= $midia->getFile()->getMimeType() ?>">
                                                        </video>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                                <?php
                                                if ($midia->isSound()) {
                                                    ?>
                                                    <div class="overlay-content">
                                                        <audio controls>
                                                            <source src="<?= $midia->getFileUrl() ?>"
                                                                    type="<?= $midia->getFile()->getMimeType() ?>">
                                                        </audio>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="file-name"><?= $midia->getFile()->getName() ?></div>
                                            <div class="controls">
                                                <ul>
                                                    <li><a data-id="<?= $midia->primaryKey() ?>"
                                                           class="delete icon-trash"></a></li>
                                                    <li><a data-id="<?= $midia->primaryKey() ?>"
                                                           class="update icon-quill4"></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    $current['html'] = ob_get_clean();
                                    $current['model'] = $midia;
                                    $response[] = $current;
                                }
                            }
                        }
                    }
                }
            }
            return ['status' => $status, 'data' => $response, 'update' => post('id')];
        }
        return ['status' => false, 'data' => []];
    }
}