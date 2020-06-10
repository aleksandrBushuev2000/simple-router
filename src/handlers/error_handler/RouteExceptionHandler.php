<?php

namespace SimpleRouter\handlers\error_handler;

use SimpleRouter\exceptions\RouteException;
use SimpleRouter\request\Request;

class RouteExceptionHandler extends AbstractRouteErrorHandler {
    public function handle(Request $req) {
        /**
         * @var RouteException $e
        */
        $e = $this->thrownError;
        http_response_code($e->getCode());
        $errorString = "An error was thrown: ".$e->getCode(). " ".$e->getMessage();
        print($errorString);
    }
}