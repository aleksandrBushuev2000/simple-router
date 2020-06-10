<?php

namespace SimpleRouter\route_comparator;

class RoutePartComparationResult {
    private bool $equal;
    private string $name;
    private string $key;
    private $value;
    private bool $isAlias;
    private int $foundAt;

    private array $optionalParams;

    public function __construct() {
        $this->equal = false;
        $this->name = "";
        $this->key = "";
        $this->value = "";
        $this->isAlias = false;
        $this->foundAt = -1;
        $this->optionalParams = array();
    }

    public function getOptionalParams() : array {
        return $this->optionalParams;
    }

    public function setOptionalParams(array $params) : RoutePartComparationResult {
        $this->optionalParams = $params;
        return $this;
    }

    public function getIsEqual() : bool {
        return $this->equal;
    }

    public function setIsEqual(bool $isEqual) : RoutePartComparationResult {
        $this->equal = $isEqual;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : RoutePartComparationResult {
        $this->name = $name;
        return $this;
    }

    public function getKey() : string {
        return $this->key;
    }

    public function setKey(string $key) : RoutePartComparationResult {
        $this->key = $key;
        return $this;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) : RoutePartComparationResult {
        $this->value = $value;
        return $this;
    }

    public function getIsAlias() : bool {
        return $this->isAlias;
    }

    public function setIsAlias(bool $isAlias) : RoutePartComparationResult {
        $this->isAlias = $isAlias;
        return $this;
    }

    public function getFoundAt() : int {
        return $this->foundAt;
    }

    public function setFoundAt(int $foundAt) : RoutePartComparationResult {
        $this->foundAt = $foundAt;
        return $this;
    }
}