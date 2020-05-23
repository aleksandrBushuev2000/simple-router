<?php

namespace SimpleRouter\template;

/**
 * @class Template
 * @author Aleksandr Bushuev
 * @version 1.0.0
 * @description Implementation of route template
*/
class Template {

    private $length;
    private $parts;
    private $handler;
    private $optionalLength;

    private $requiredIndexes = array();
    private $optionalIndexes = array();

    public function __construct($handler) {
        $this->handler = $handler;
        $this->length = 0;
        $this->parts = array();
    }

    public function __get($key) {
        if (isset($this->$key)) {
            return $this->$key;
        }
        return null;
    }


    public function push($part) {
        if ($part != null) {
            array_push($this->parts, $part);
            $this->length++;
            if ($part->isOptional) {
                array_push($this->optionalIndexes, $this->length - 1);
                $this->optionalLength++;
            } else {
                array_push($this->requiredIndexes, $this->length - 1);
            }
            return true;
        }
        return false;
    }
}