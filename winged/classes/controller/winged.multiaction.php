<?php

class MultiAction
{

    public $multi = [];

    public function __construct()
    {

    }


    public function registerMultiAction($action_string_id, $action_desc){
        $a = [
            'url_match' => 'value/value',
            'controller' => 'NameController',
            'action' => 'actionAction',
        ];
    }

}