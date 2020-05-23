<?php

namespace SimpleRouter\handlers\default_handlers;

use SimpleRouter\handlers\IRequestHandler;

class InternalServeErrorHandler implements IRequestHandler {
    public function handle($req) {
        http_response_code(500);
        echo "Internal Server Error";
    }
}