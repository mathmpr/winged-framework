<?php

namespace Winged\Assets;

use Winged\Controller\Controller;
use Winged\Winged;

/**
 * Class Assets
 * @package Winged\Assets
 */
class Assets
{
    /**
     * @var $controller Controller
     */
    private $controller = null;

    public function __construct(Controller $controller = null)
    {
        $this->controller = $controller;
    }

    /**
     * @return $this
     */
    public function admin()
    {
        $this->controller->rewriteHeadContentPath(Winged::$parent . '/head.content.php');

        /*<core css>*/
        $this->controller->addCss("roboto", "https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900", [], true);
        $this->controller->addCss("bootstrap", Winged::$parent . "assets/css/core/files/bootstrap.css");
        $this->controller->addCss("font-awesome", Winged::$parent . "assets/css/core/files/font-awesome.css");
        $this->controller->addCss("components", Winged::$parent . "assets/css/core/files/components.css");
        $this->controller->addCss("colors", Winged::$parent . "assets/css/core/files/colors.css");
        $this->controller->addCss("core", Winged::$parent . "assets/css/core/core.css");
        /*<end core css>*/

        /*<custom>*/
        $this->controller->addCss("croppic", Winged::$parent . "assets/ext/croppie/jcrop.css");
        /*<end custom>*/

        $this->controller->addJs("beggin", Winged::initialJs());

        /*<core js>*/
        $this->controller->addJs("jquery", Winged::$parent . "assets/js/core/files/libraries/jquery.min.js");
        $this->controller->addJs("jquery-ui", Winged::$parent . "assets/js/core/files/jquery-ui.min.js");
        $this->controller->addJs("pace", Winged::$parent . "assets/js/plugins/loaders/pace.min.js");
        $this->controller->addJs("bootstrap", Winged::$parent . "assets/js/core/files/libraries/bootstrap.min.js");
        $this->controller->addJs("blockui", Winged::$parent . "assets/js/plugins/loaders/blockui.min.js");
        $this->controller->addJs("jscookie", Winged::$parent . "assets/js/core/files/js.cookie.js");
        /*<end core js>*/

        /*<fileupload js>*/
        $this->controller->addJs("fileupload-widget", Winged::$parent . "assets/js/plugins/fileupload/jquery.ui.widget.js");
        $this->controller->addJs("fileupload-canvas", Winged::$parent . "assets/js/plugins/fileupload/canvas.to.blob.js");
        $this->controller->addJs("fileupload-load", Winged::$parent . "assets/js/plugins/fileupload/load-image.all.js");
        $this->controller->addJs("fileupload-iframe", Winged::$parent . "assets/js/plugins/fileupload/jquery.iframe-transport.js");
        $this->controller->addJs("fileupload-main", Winged::$parent . "assets/js/plugins/fileupload/jquery.fileupload.js");
        $this->controller->addJs("fileupload-process", Winged::$parent . "assets/js/plugins/fileupload/jquery.fileupload-process.js");
        $this->controller->addJs("fileupload-image", Winged::$parent . "assets/js/plugins/fileupload/jquery.fileupload-image.js");
        $this->controller->addJs("fileupload-audio", Winged::$parent . "assets/js/plugins/fileupload/jquery.fileupload-audio.js");
        $this->controller->addJs("fileupload-video", Winged::$parent . "assets/js/plugins/fileupload/jquery.fileupload-video.js");
        $this->controller->addJs("fileupload-validate", Winged::$parent . "assets/js/plugins/fileupload/jquery.fileupload-validate.js");
        $this->controller->addJs("fileupload-ui", Winged::$parent . "assets/js/plugins/fileupload/jquery.fileupload-ui.js");
        /*<end fileupload js>*/

        /*<custom>*/
        $this->controller->addJs("croppic", Winged::$parent . "assets/ext/croppie/jcrop.js");
        $this->controller->addJs("pnotify", Winged::$parent . "assets/js/plugins/notifications/pnotify.min.js");
        $this->controller->addJs("mask", Winged::$parent . "assets/js/core/files/mask.js");
        $this->controller->addJs("maskmoney", Winged::$parent . "assets/js/core/files/maskmoney.js");
        $this->controller->addJs("evaluate", Winged::$parent . "assets/js/core/files/evaluate.js");
        $this->controller->addJs("validate", Winged::$parent . "assets/js/plugins/forms/validation/validate.min.js");
        $this->controller->addJs("handlebars", Winged::$parent . "assets/js/plugins/forms/inputs/typeahead/handlebars.min.js");
        $this->controller->addJs("alpaca", Winged::$parent . "assets/js/plugins/forms/inputs/alpaca/alpaca.min.js");
        $this->controller->addJs("uniform", Winged::$parent . "assets/js/plugins/forms/styling/uniform.min.js");
        $this->controller->addJs("summernote", Winged::$parent . "assets/js/plugins/editors/summernote/summernote.min.js");
        $this->controller->addJs("summernote-lang", Winged::$parent . "assets/js/plugins/editors/summernote/lang/summernote-pt-BR.js");
        $this->controller->addJs("select", Winged::$parent . "assets/js/plugins/forms/selects/select2.min.js");
        $this->controller->addJs("validate_lang", Winged::$parent . "assets/js/plugins/forms/validation/localization/messages_pt_PT.js");
        $this->controller->addJs("checkbox_switchery", Winged::$parent . "assets/js/plugins/forms/styling/switchery.min.js");
        $this->controller->addJs("checkbox_switch", Winged::$parent . "assets/js/plugins/forms/styling/switch.min.js");
        $this->controller->addJs("search", Winged::$parent . "assets/js/core/search.js");
        $this->controller->addJs("admin.class", Winged::$parent . "assets/js/core/admin.class.js");
        $this->controller->addJs("bootbox", Winged::$parent . "assets/js/plugins/notifications/bootbox.min.js");
        $this->controller->addJs("sweet_alert", Winged::$parent . "assets/js/plugins/notifications/sweet_alert.min.js");
        $this->controller->addJs("moment", Winged::$parent . "assets/js/plugins/ui/moment/moment.min.js");
        $this->controller->addJs("moment_locales", Winged::$parent . "assets/js/plugins/ui/moment/moment_locales.min.js");
        $this->controller->addJs("daterangepicker", Winged::$parent . "assets/js/plugins/pickers/daterangepicker.js");
        $this->controller->addJs("anytime", Winged::$parent . "assets/js/plugins/pickers/anytime.min.js");
        $this->controller->addJs("pickadate", Winged::$parent . "assets/js/plugins/pickers/pickadate/picker.js");
        $this->controller->addJs("pickadatedate", Winged::$parent . "assets/js/plugins/pickers/pickadate/picker.date.js");
        $this->controller->addJs("pickadatetime", Winged::$parent . "assets/js/plugins/pickers/pickadate/picker.time.js");
        $this->controller->addJs("legacy", Winged::$parent . "assets/js/plugins/pickers/pickadate/legacy.js");
        $this->controller->addJs("default", Winged::$parent . "assets/js/pages/default.js");
        $this->controller->addJs("globals", "<script>let PAGE_SURNAME = \"". Winged::$page_surname ."\";</script>");
        /*<end custom>*/

        /*<core>*/
        $this->controller->addJs("core", Winged::$parent . "assets/js/core/core.js");
        /*<end core>*/

        return $this;
    }
}