<?php

namespace SimpleRouter\exceptions;

class UndefinedHttpMethodException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}