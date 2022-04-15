<?php


namespace SimpleRouter\response\impl;


use SimpleRouter\response\ResponseInterface;
use SimpleRouter\response\utils\ResponseCookie;
use SimpleRouter\response\utils\ResponseHeader;

abstract class AbstractResponse implements ResponseInterface
{
    protected int $statusCode = 200;

    protected array $headers = [];
    protected array $cookies = [];
    
    public function setStatusCode(int $status): self {
        $this->statusCode = $status;
        return $this;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function setHeader(ResponseHeader $header): self {
        $this->headers[$header->getName()] = $header;
        return $this;
    }

    public function getHeader(string $headerName): ?ResponseHeader {
        return $this->headers[$headerName] ?? null;
    }

    public function hasHeader(string $headerName): bool {
        return isset($this->headers[$headerName]);
    }

    public function removeHeader(string $headerName): bool {
        if ($this->hasHeader($headerName)) {
            unset($this->headers[$headerName]);
            return true;
        }
        return false;
    }

    public function setCookie(ResponseCookie $cookie): self {
        $this->cookies[$cookie->getName()] = $cookie;
        return $this;
    }

    public function getCookie(string $cookieName): ?ResponseCookie {
        return $this->cookies[$cookieName] ?? null;
    }

    public function hasCookie(string $cookieName): bool {
        return isset($this->cookies[$cookieName]);
    }

    public function removeCookie(string $cookieName): bool {
        if ($this->hasCookie($cookieName)) {
            unset($this->cookies[$cookieName]);
            return true;
        }
        return false;
    }

    public function send(): void {
        $this->sendStatus();
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }

    protected abstract function sendBody();

    private function sendStatus() {
        http_response_code($this->statusCode);
    }

    private function sendHeaders() {
        foreach ($this->headers as $header) {
            header(strval($header), true);
        }
    }

    private function sendCookies() {
        foreach ($this->cookies as $_ => $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpires(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }
}