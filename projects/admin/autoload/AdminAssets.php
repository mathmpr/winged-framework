<?php

use Winged\Winged;

/**
 * Class AdminAssets
 */
class AdminAssets
{

    /**
     * @param $controller \Winged\Controller\Controller
     */
    public static function init($controller = null)
    {
        if ($controller) {
            /*<core css>*/
            $controller->appendCss("roboto", "https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900", [], true);
            $controller->appendCss("bootstrap", Winged::$parent . "assets/css/core/files/bootstrap.css");
            $controller->appendCss("components", Winged::$parent . "assets/css/core/files/components.css");
            $controller->appendCss("colors", Winged::$parent . "assets/css/core/files/colors.css");
            $controller->appendCss("core", Winged::$parent . "assets/css/core/core.css");
            /*<end core css>*/

            /*<custom>*/
            $controller->appendCss("croppic", Winged::$parent . "assets/ext/croppie/jcrop.css");
            /*<end custom>*/

            ob_start();
            ?>
            <script>
                window.protocol = "<?= Winged::$protocol ?>";
                window.page_surname = "<?= Winged::$page_surname ?>";
                window.uri = "<?= Winged::$uri ?>";
                window.controller_params = JSON.parse('<?= json_encode(Winged::$controller_params) ?>');
                window.controller_action = "<?= Winged::$controller_action ?>";
            </script>
            <?php
            $controller->appendJs("begin", ob_get_clean());

            /*<core js>*/
            $controller->appendJs("jquery", Winged::$parent . "assets/js/core/files/libraries/jquery.min.js");
            $controller->appendJs("pace", Winged::$parent . "assets/js/plugins/loaders/pace.min.js");
            $controller->appendJs("bootstrap", Winged::$parent . "assets/js/core/files/libraries/bootstrap.min.js");
            $controller->appendJs("blockui", Winged::$parent . "assets/js/plugins/loaders/blockui.min.js");
            $controller->appendJs("jscookie", Winged::$parent . "assets/js/core/files/js.cookie.js");
            /*<end core js>*/

            /*<custom>*/
            $controller->appendJs("croppic", Winged::$parent . "assets/ext/croppie/jcrop.js");
            $controller->appendJs("pnotify", Winged::$parent . "assets/js/plugins/notifications/pnotify.min.js");
            $controller->appendJs("mask", Winged::$parent . "assets/js/core/files/mask.js");
            $controller->appendJs("maskmoney", Winged::$parent . "assets/js/core/files/maskmoney.js");
            $controller->appendJs("evaluate", Winged::$parent . "assets/js/core/files/evaluate.js");
            $controller->appendJs("validate", Winged::$parent . "assets/js/plugins/forms/validation/validate.min.js");
            $controller->appendJs("handlebars", Winged::$parent . "assets/js/plugins/forms/inputs/typeahead/handlebars.min.js");
            $controller->appendJs("alpaca", Winged::$parent . "assets/js/plugins/forms/inputs/alpaca/alpaca.min.js");
            $controller->appendJs("uniform", Winged::$parent . "assets/js/plugins/forms/styling/uniform.min.js");
            $controller->appendJs("summernote", Winged::$parent . "assets/js/plugins/editors/summernote/summernote.min.js");
            $controller->appendJs("summernote-lang", Winged::$parent . "assets/js/plugins/editors/summernote/lang/summernote-pt-BR.js");
            $controller->appendJs("select", Winged::$parent . "assets/js/plugins/forms/selects/select2.min.js");
            $controller->appendJs("validate_lang", Winged::$parent . "assets/js/plugins/forms/validation/localization/messages_pt_PT.js");
            $controller->appendJs("checkbox_switchery", Winged::$parent . "assets/js/plugins/forms/styling/switchery.min.js");
            $controller->appendJs("checkbox_switch", Winged::$parent . "assets/js/plugins/forms/styling/switch.min.js");
            $controller->appendJs("search", Winged::$parent . "assets/js/core/search.js");
            $controller->appendJs("admin.class", Winged::$parent . "assets/js/core/admin.class.js");
            $controller->appendJs("bootbox", Winged::$parent . "assets/js/plugins/notifications/bootbox.min.js");
            $controller->appendJs("sweet_alert", Winged::$parent . "assets/js/plugins/notifications/sweet_alert.min.js");
            /*<end custom>*/

            /*<core>*/
            $controller->appendJs("core", Winged::$parent . "assets/js/core/core.js");
            /*<end core>*/
        }
    }

}