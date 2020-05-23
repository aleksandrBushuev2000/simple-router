<?php

namespace SimpleRouter\handlers\default_handlers;

use SimpleRouter\handlers\IRequestHandler;

class NotFoundHandler implements IRequestHandler {
    public function handle($req) {
        http_response_code(404);
        echo "Cannot ".$_SERVER['REQUEST_METHOD']." ".$_SERVER['REQUEST_URI'];
    }
}