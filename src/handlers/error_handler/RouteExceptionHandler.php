<?php

namespace SimpleRouter\handlers\error_handler;

use Exception;
use SimpleRouter\request\Request;
use SimpleRouter\response\impl\EmptyResponse;
use SimpleRouter\response\impl\PlainTextResponse;
use SimpleRouter\response\ResponseInterface;
use SimpleRouter\Router;

class RouteExceptionHandler extends AbstractRouteErrorHandler {
    public function handle(Request $req) : ResponseInterface {
        $e = $this->thrownError;
        if (Router::getMode() == 'dev') {
            return $this->handleDev($e);
        }
        return $this->handleProd($e);
    }

    private function handleProd(Exception $e) : ResponseInterface {
        return (new EmptyResponse())->setStatusCode($e->getCode());
    }

    private function handleDev(Exception $e) : ResponseInterface {
        $message = "An error was thrown: ".$e->getCode(). " ".$e->getMessage();
        $file = $e->getFile();
        $line = strval($e->getLine());
        $stack = $e->getTraceAsString();

        $payload = "Message: $message\n";
        $payload .= "File: $file, line: $line\n";
        $payload .= "Stack trace: \n$stack";

        return new PlainTextResponse($payload);
    }
}