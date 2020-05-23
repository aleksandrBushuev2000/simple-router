<?php

namespace SimpleRouter\handlers\default_handlers;

use SimpleRouter\handlers\IRequestHandler;

class BadRequestHandler implements IRequestHandler {
    public function handle($req) {
        http_response_code(400);
        echo "Bad Request";
    }
}