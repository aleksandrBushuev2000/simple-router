<?php

namespace SimpleRouter\request_parser;

use SimpleRouter\request\Request;

/**
 * @class DefaultRequestParser
 * @version 1.00
 * @author AleksandrBushuev
 * @description It parses http input data and builds Request Object
*/
class DefaultRequestParser implements IRequestParser {

    private function getHeaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } else if ($name == "CONTENT_TYPE") {
                $headers["Content-Type"] = $value;
            } else if ($name == "CONTENT_LENGTH") {
                $headers["Content-Length"] = $value;
            }
       }
       return $headers;
    }

    private function parseQueryString() {
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

    private function getRequestBody($req) {
        $contentType = $req->headers["Content-Type"];
        $req->body = array();
        $req->files = array();
        if ($contentType) {
            if (strpos($contentType, "application/json") !== false) {
                $req->body = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
                $req->files = array();
            } else if ($contentType === "multipart/form-data") {
                parse_str(file_get_contents("php://input"), $req->body);
                $req->files = $_FILES;
            } else {
                parse_str(file_get_contents("php://input"), $req->body);
            }
        }

        return $req;
    }

    public function parse() {
        $pathAndQuery = $this->parseQueryString();
        $req = new Request();
        $req->method = $_SERVER['REQUEST_METHOD'];
        $req->path = $pathAndQuery->path;
        $req->query = $pathAndQuery->query;
        $req->headers = $this->getHeaders();
        $req = $this->getRequestBody($req);
        $req->cookie = $_COOKIE;
        $req->params = array();
        return $req;
    }
}