<?php

namespace SimpleRouter\exceptions;

class InternalServeErrorException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}