<?php

namespace Winged\Form;

class FormHtmlStore
{
    public static $ELEMENTS = [
        'select' => [
            'html' => '<div>
                          <label></label>
                          <div class="relative"><select class="form-control"></select></div>
                          <label style="display: none" class="error validation-error-label"></label>
                       </div>',
            'main' => 'select',
            'main_selector' => 'select',
        ],
        'text' => [
            'html' => '<div>
                           <label></label>
                           <div class="relative"><input class="form-control" type="text"/></div>
                           <label style="display: none" class="error validation-error-label"></label>
                       </div>',
            'main' => 'input',
            'main_selector' => 'input[type=text]',
        ],
        'password' => [
            'html' => '<div>
                           <label></label>
                           <div class="relative"><input class="form-control" type="password"/></div>
                           <label style="display: none" class="error validation-error-label"></label>
                       </div>',
            'main' => 'input',
            'main_selector' => 'input[type=password]',
        ],
        'textarea' => [
            'html' => '<div>
                           <label></label>
                           <div class="relative"><textarea class="form-control"></textarea></div>
                           <label style="display: none" class="error validation-error-label"></label>
                       </div>',
            'main' => 'textarea',
            'main_selector' => 'textarea',
        ],
        'summernote' => [
            'html' => '<div>
                           <label></label>
                           <div class="relative"><textarea class="summernote"></textarea></div>
                           <label style="display: none" class="error validation-error-label"></label>
                       </div>',
            'main' => 'textarea',
            'main_selector' => 'textarea',
        ],
        'checkbox' => [
            'html' => '<div>
                           <label></label>
                           <div class="relative"><label style="display: none" class="error validation-error-label"></label></div>
                           <div class="checkbox">
                               
                           </div>
                       </div>',
            'main' => 'checkbox',
            'main_selector' => '.checkbox',
        ],
        'radio' => [
            'html' => '<div>
                           <label></label>
                           <label style="display: none" class="error validation-error-label"></label>
                           <div class="radio">
                               
                           </div>
                       </div>',
            'main' => 'radio',
            'main_selector' => '.radio',
        ],
        'onoff' => [
            'html' => '<div class="col-lg-12">
                            <label class="display-block"></label>
                            <div class="checkbox checkbox-switchery">
                                <label>
                                    <input class="switchery" type="checkbox">
                                </label>
                            </div>
                        </div>',
            'main' => 'input',
            'main_selector' => 'input[type=checkbox]',
        ],
        'bsubmit' => [
            'html' => '<button type="submit"></button>',
            'main' => 'button',
            'main_selector' => 'button[type=submit]',
        ],
        'hidden' => [
            'html' => '<input type="hidden"/>',
            'main' => 'input',
            'main_selector' => 'input[type=hidden]',
        ],
    ];
}