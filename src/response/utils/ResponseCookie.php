<?php


namespace SimpleRouter\response\utils;


class ResponseCookie {

    private string $name;
    private string $value;
    private int $expires;
    private string $path = "";
    private string $domain = "";
    private bool $secure = false;
    private bool $httpOnly = false;

    public static function create(string $name, $value) : self {
        return new ResponseCookie($name, $value);
    }

    public function __construct(string $name, $value) {
        $this->name = $name;
        $this->value = strval($value);
        $this->expires = time();
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

    /**
     * @return string
     */
    public function getDomain(): string {
        return $this->domain;
    }

    /**
     * @return int
     */
    public function getExpires(): int {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool {
        return $this->httpOnly;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool {
        return $this->secure;
    }

    /**
     * @param string $domain
     * @return ResponseCookie
     */
    public function setDomain(string $domain): self {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param int $expires
     * @return ResponseCookie
     */
    public function setExpires(int $expires): self {
        $this->expires = $expires;
        return $this;
    }

    /**
     * @param bool $httpOnly
     * @return ResponseCookie
     */
    public function setHttpOnly(bool $httpOnly): self {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    /**
     * @param string $path
     * @return ResponseCookie
     */
    public function setPath(string $path): self {
        $this->path = $path;
        return $this;
    }

    /**
     * @param bool $secure
     * @return ResponseCookie
     */
    public function setSecure(bool $secure): self {
        $this->secure = $secure;
        return $this;
    }
}