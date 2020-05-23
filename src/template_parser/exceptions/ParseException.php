<?php

namespace SimpleRouter\template_parser\exceptions;

class ParseException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}