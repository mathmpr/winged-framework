<?php

namespace Winged\External\PhpQuery;

/**
 * Class CallbackBody
 * @package Winged\External\PhpQuery
 */
class CallbackBody extends Callback {
    /**
     * CallbackBody constructor.
     * @param $paramList
     * @param null $code
     * @param null $param1
     * @param null $param2
     * @param null $param3
     */
    public function __construct($paramList, $code, $param1 = null, $param2 = null,
                                $param3 = null) {
        $params = func_get_args();
        $params = array_slice($params, 2);
        $this->callback = create_function($paramList, $code);
        $this->params = $params;
    }
}