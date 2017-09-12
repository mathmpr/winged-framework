<?php

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

    public function admin()
    {
        $this->controller->rewriteHeadContentPath('./admin/head.content.php');

        /*<core css>*/
        $this->controller->addCss('roboto', 'https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900', true);
        $this->controller->addCss("bootstrap", "./admin/assets/css/core/files/bootstrap.css");
        $this->controller->addCss("font-awesome", "./admin/assets/css/core/files/font-awesome.css");
        $this->controller->addCss("components", "./admin/assets/css/core/files/components.css");
        $this->controller->addCss("colors", "./admin/assets/css/core/files/colors.css");
        $this->controller->addCss("core", "./admin/assets/css/core/core.css");
        /*<end core css>*/

        $this->controller->addJs("beggin", Winged::initialJs());

        /*<core js>*/
        $this->controller->addJs("pace", "./admin/assets/js/plugins/loaders/pace.min.js");
        $this->controller->addJs("jquery", "./admin/assets/js/core/files/libraries/jquery.min.js");
        $this->controller->addJs("bootstrap", "./admin/assets/js/core/files/libraries/bootstrap.min.js");
        $this->controller->addJs("blockui", "./admin/assets/js/plugins/loaders/blockui.min.js");
        $this->controller->addJs("jscookie", "./admin/assets/js/core/files/js.cookie.js");
        /*<end core js>*/

        /*<custom>*/

        $this->controller->addJs("pnotify", "./admin/assets/js/plugins/notifications/pnotify.min.js");
        $this->controller->addJs("mask", "./admin/assets/js/core/files/mask.js");
        $this->controller->addJs("maskmoney", "./admin/assets/js/core/files/maskmoney.js");
        $this->controller->addJs("evaluate", "./admin/assets/js/core/files/evaluate.js");
        $this->controller->addJs("validate", "./admin/assets/js/plugins/forms/validation/validate.min.js");
        $this->controller->addJs("select", "./admin/assets/js/plugins/forms/selects/select2.min.js");
        $this->controller->addJs("validate_lang", "./admin/assets/js/plugins/forms/validation/localization/messages_pt_PT.js");
        $this->controller->addJs("checkbox_uniform", "./admin/assets/js/plugins/forms/styling/uniform.min.js");
        $this->controller->addJs("checkbox_switchery", "./admin/assets/js/plugins/forms/styling/switchery.min.js");
        $this->controller->addJs("checkbox_switch", "./admin/assets/js/plugins/forms/styling/switch.min.js");
        $this->controller->addJs("search", "./admin/assets/js/core/search.js");
        $this->controller->addJs("admin.class", "./admin/assets/js/core/admin.class.js");
        $this->controller->addJs("bootbox", "./admin/assets/js/plugins/notifications/bootbox.min.js");
        $this->controller->addJs("sweet_alert", "./admin/assets/js/plugins/notifications/sweet_alert.min.js");
        /*<end custom>*/

        /*<core>*/
        $this->controller->addJs("core", "./admin/assets/js/core/core.js");
        /*<end core>*/

        return $this;
    }
}