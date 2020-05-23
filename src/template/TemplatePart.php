<?php

namespace SimpleRouter\template;

use SimpleRouter\template\exceptions\TemplatePartException;

/**
 * @class TemplatePart
 * @author Aleksandr Bushuev
 * @version 1.0.0
 * @description Implementation of route template part
 */

class TemplatePart {

    private $name;
    private $isAlias;
    private $isOptional;
    private $dataType;
    private $initialValue;

    public function __set($key, $value) {
        if ($key == "name" && !$value) {
            throw new TemplatePartException("Empty name");
        }
        $this->$key = $value;
        return $this;
    }
    
    public function __call($method, $arg) {
        $this->$method = $arg[0];
        return $this;
    }

    public function __get($key) {
        return $this->$key;
    }
}