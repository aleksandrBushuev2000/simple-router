<?php

namespace SimpleRouter\template\exceptions;

class TemplatePartException extends \Exception {
    function __construct($message) {
        parent::__construct($message);
    }
}