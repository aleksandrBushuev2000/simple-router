<?php


namespace SimpleRouter\response\impl;


use SimpleRouter\response\utils\ResponseHeader;

class JsonResponse extends AbstractResponse {

    /**
     * @var mixed $payload
    */
    private $payload;

    public function __construct($payload) {
        $this->payload = $payload;
        $this->setHeader(ResponseHeader::create("Content-Type", "application/json"));
    }

    /**
     * @throws \JsonException
     */
    protected function sendBody() {
        $rawPayload = json_encode($this->payload, JSON_THROW_ON_ERROR, 1024);
        echo $rawPayload;
    }
}