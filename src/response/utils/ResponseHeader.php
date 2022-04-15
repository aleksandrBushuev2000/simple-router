<?php


namespace SimpleRouter\response\utils;


class ResponseHeader {

    private string $name;
    private string $value;

    public static function create(string $name, string $value) : self {
        return new ResponseHeader($name, $value);
    }

    public function __construct(string $name, string $value) {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string {
        return $this->value;
    }

    public function __toString() : string {
        $name = strtolower($this->name);
        $value = strtolower($this->value);
        return "$name : $value";
    }
}