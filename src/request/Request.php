<?php

namespace SimpleRouter\request;

class Request {
    public $method;
    public $path;
    public $query;
    public $body;
    public $params;
    public $headers;
    public $files;
    public $cookie;
}