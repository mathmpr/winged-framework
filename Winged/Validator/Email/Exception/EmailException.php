<?php

namespace Winged\Validator\Email;

/**
 * Email exception handler
 */
class EmailException extends \Exception {

    /**
     * Prettify error message output
     * @return string
     */
    public function errorMessage() {
        $errorMsg = $this->getMessage();
        return $errorMsg;
    }

}