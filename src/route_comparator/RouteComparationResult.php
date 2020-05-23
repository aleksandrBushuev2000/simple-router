<?php

namespace SimpleRouter\route_comparator;

class RouteComparationResult {
    private $lengthDelta;
    private $params;
    private $handler;

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