<?php

namespace SimpleRouter\exceptions;

use Exception;

class RouteException extends Exception {
    public function __construct($message = "", $code = 500) {
        $code = $this->validateCode($code);
        parent::__construct($message, $code);
    }

    private function validateCode($code) {
        if ($code < 400 || $code > 599) {
            return 500;
        }
        return $code;
    }
}