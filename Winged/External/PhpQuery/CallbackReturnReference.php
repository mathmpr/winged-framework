<?php

namespace Winged\External\PhpQuery;


/**
 * Callback type which on execution returns reference passed during creation.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackReturnReference extends Callback
    implements ICallbackNamed
{
    protected $reference;

    /**
     * CallbackReturnReference constructor.
     * @param $reference
     * @param null $name
     */
    public function __construct(&$reference, $name = null)
    {
        $this->reference =& $reference;
        $this->callback = array($this, 'callback');
    }

    /**
     * @return mixed
     */
    public function callback()
    {
        return $this->reference;
    }

    public function getName()
    {
        return 'Callback: ' . $this->name;
    }

    public function hasName()
    {
        return isset($this->name) && $this->name;
    }
}