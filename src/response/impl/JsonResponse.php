<?php


namespace SimpleRouter\response\impl;


class JsonResponse extends AbstractResponse {

    /**
     * @var mixed $payload
    */
    private $payload;

    public function __construct($payload) {
        $this->payload = $payload;
    }

    /**
     * @throws \JsonException
     */
    protected function sendBody() {
        $rawPayload = json_encode($this->payload, JSON_THROW_ON_ERROR, 1024);
        echo $rawPayload;
    }
}