<?php

namespace SimpleRouter\route_comparator;

class RoutePartComparationResult {
    private $equal;
    private $name;
    private $key;
    private $value;
    private $isAlias;
    private $foundAt;

    public function __get($key) {
        return $this->$key;
    }

    public function __call($method, $arg) {
        $this->$method = $arg[0];
        return $this;
    }

    public function __set($key, $value) {
        $this->$key = $value;
        return $this;
    }
}