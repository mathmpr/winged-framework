<?php

use Winged\Utils\RandomName;
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
            //$controller->appendCss("roboto", "https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900", [], true);
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
                window._parent = "<?= Winged::$parent ?>";
                window.uri = "<?= Winged::$uri ?>";
                window.controller_params = JSON.parse('<?= json_encode(Winged::$controller_params) ?>');
                window.controller_action = "<?= Winged::$controller_action ?>";
            </script>
            <?php
            $controller->appendJs("begin", ob_get_clean());

            /*<core js>*/
            $controller->appendJs("jquery", Winged::$parent . "assets/js/core/files/libraries/jquery.min.js");
            $controller->appendJs("jquery-ui", Winged::$parent . "assets/js/core/files/libraries/jquery_ui/full.min.js");
            $controller->appendJs("pace", Winged::$parent . "assets/js/plugins/loaders/pace.min.js");
            $controller->appendJs("bootstrap", Winged::$parent . "assets/js/core/files/libraries/bootstrap.min.js");
            $controller->appendJs("blockui", Winged::$parent . "assets/js/plugins/loaders/blockui.min.js");
            $controller->appendJs("jscookie", Winged::$parent . "assets/js/core/files/js.cookie.js");
            /*<end core js>*/

            /*<custom>*/
            $controller->appendJs("moment", Winged::$parent . "assets/js/plugins/ui/moment/moment.min.js");
            $controller->appendJs("daterangepicker", Winged::$parent . "assets/js/plugins/pickers/daterangepicker.js");
            $controller->appendJs("anytime", Winged::$parent . "assets/js/plugins/pickers/anytime.min.js");
            $controller->appendJs("picker", Winged::$parent . "assets/js/plugins/pickers/pickadate/picker.js");
            $controller->appendJs("picker-date", Winged::$parent . "assets/js/plugins/pickers/pickadate/picker.date.js");
            $controller->appendJs("picker-time", Winged::$parent . "assets/js/plugins/pickers/pickadate/picker.time.js");
            $controller->appendJs("leagcy", Winged::$parent . "assets/js/plugins/pickers/pickadate/legacy.js");

            $controller->appendJs("plupload-full", Winged::$parent . "assets/js/plugins/uploaders/plupload/plupload.full.min.js");
            $controller->appendJs("plupload-queue", Winged::$parent . "assets/js/plugins/uploaders/plupload/plupload.queue.min.js");
            $controller->appendJs("plupload-lang", Winged::$parent . "assets/js/plugins/uploaders/plupload/i18n/pt_BR.js");
            $controller->appendJs("jquery-colors", Winged::$parent . "assets/js/plugins/media/jquery.color.js");
            $controller->appendJs("jcrop", Winged::$parent . "assets/js/plugins/media/jquery.Jcrop.min.js");
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
            $controller->appendJs('tokenstags', Winged::$parent . 'assets/js/pages/tokenstags.js');
            $controller->appendJs('numeric', Winged::$parent . 'assets/js/pages/numeric.js');
            /*<end custom>*/

            /*<core>*/
            $controller->appendJs("core", Winged::$parent . "assets/js/core/core.js");
            $controller->appendJs("core-default", Winged::$parent . "assets/js/pages/default.js");
            /*<end core>*/
        }
    }

    /**
     * @param $content
     */
    public static function compactHtml(&$content){
        $rep = [];
        $match = false;
        $matchs = null;
        if (is_int(stripos($content, '<textarea'))) {
            $match = preg_match_all('#<textarea(.*?)>(.*?)</textarea>#is', $content, $matchs);
            if ($match) {
                foreach ($matchs[0] as $match) {
                    $rep[] = '#___' . RandomName::generate('sisisisi') . '___#';
                }
                $content = str_replace($matchs[0], $rep, $content);
            }
        }
        $match_code = false;
        $matchs_code = null;
        if (is_int(stripos($content, '<code'))) {
            $match_code = preg_match_all('#<code(.*?)>(.*?)</code>#is', $content, $matchs_code);
            if ($match_code) {
                foreach ($matchs_code[0] as $match_code) {
                    $rep[] = '#___' . RandomName::generate('sisisisi') . '___#';
                }
                $content = str_replace($matchs_code[0], $rep, $content);
            }
        }
        $content = preg_replace('#> <#', '><', preg_replace('# {2,}#', ' ', preg_replace('/[\n\r]|/', '', $content)));
        if ($match) {
            $content = str_replace($rep, $matchs[0], $content);
        }
        if ($match_code) {
            $content = str_replace($rep, $matchs_code[0], $content);
        }
    }

}