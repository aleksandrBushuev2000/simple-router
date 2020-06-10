<?php

namespace SimpleRouter\request;

/**
 * @class Request
 * @version 2.0.0
 * @author Aleksandr Bushuev
 * @description Request instance
*/
class Request {
    private string $method;
    private array $headers;
    private string $path;
    private array $query;
    private array $params;
    private array $cookie;

    private array $vars;

    private function getHeaders() : array {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ',
                    '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } else if ($name == "CONTENT_TYPE") {
                $headers["Content-Type"] = $value;
            } else if ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"] = $value;
            }
        }
        return $headers;
    }

    private function getCookies() {
        if (isset($_COOKIE)) {
            return $_COOKIE;
        }
        return [];
    }

    private function parseRequestPathAndQuery() {
        $reqUri = $_SERVER['REQUEST_URI'];

        $query = array();

        if (strpos($reqUri, "?") === false) {
            $path = $reqUri;
        } else {
            $pathAndQuery = explode("?", $reqUri);
            $path = $pathAndQuery[0];
            parse_str($pathAndQuery[1], $query);
        }

        return new class($path, $query) {
            public $path;
            public $query;

            public function __construct($path, $query) {
                $this->path = $path;
                $this->query = $query;
            }
        };
    }

    public static function create($params) : Request {
        $req = new Request();
        $req->method = $_SERVER['REQUEST_METHOD'];
        $req->headers = $req->getHeaders();
        $req->cookie = $req->getCookies();
        $req->params = $params;

        $pathAndQuery = $req->parseRequestPathAndQuery();
        $req->path = $pathAndQuery->path;
        $req->query = $pathAndQuery->query;

        return $req;
    }

    private function __construct() {
        $this->vars = [];
    }

    public function getRequestMethod() : string {
        return $this->method;
    }

    public function getRequestHeaders() : array {
        return $this->headers;
    }

    public function getRequestHeader(string $name) : string {
        return $this->headers[$name];
    }

    public function getRequestPath() : string {
        return $this->path;
    }

    public function getRequestQuery() : array {
        return $this->query;
    }

    public function getRequestQueryValueByKey(string $key) : ?string {
        if (isset($this->query[$key])) {
            return $this->query[$key];
        }
        return null;
    }

    public function getRequestParams() : array {
        return $this->params;
    }

    public function getRequestParamByKey(string $key) {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return null;
    }

    public function setRequestParams(array $params) : void {
        $this->params = $params;
    }

    public function getRequestCookieByKey(string $key) : ?string {
        if (isset($this->cookie[$key])) {
            return $this->cookie[$key];
        }
        return null;
    }

    public function setRequestVariable(string $key, $value) : void {
        $this->vars[$key] = $value;
    }

    public function getRequestVariableByKey(string $key) {
        if (isset($this->vars[$key])) {
            return $this->vars[$key];
        }

        return null;
    }

}