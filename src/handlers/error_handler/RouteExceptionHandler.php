<?php

namespace SimpleRouter\handlers\error_handler;

use SimpleRouter\request\Request;
use SimpleRouter\Router;

class RouteExceptionHandler extends AbstractRouteErrorHandler {
    public function handle(Request $req) {
        $e = $this->thrownError;

        http_response_code($e->getCode());
        $errorString = "An error was thrown: ".$e->getCode(). " ".$e->getMessage();
        print($errorString);

        if (Router::getMode() == 'dev') {
            print($e->getTraceAsString());
        }
    }
}