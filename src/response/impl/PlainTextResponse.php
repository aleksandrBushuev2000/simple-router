<?php


namespace SimpleRouter\response\impl;


use SimpleRouter\response\utils\ResponseHeader;

class PlainTextResponse extends AbstractResponse {

    /**
     * @var mixed $payload
     */
    private $payload;

    public function __construct($payload) {
        $this->payload = $payload;
        $this->setHeader(ResponseHeader::create("Content-Type", "text/plain"));
    }

    protected function sendBody() {
        echo $this->payload;
    }
}