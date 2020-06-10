<?php

namespace SimpleRouter\handlers\error_handler;

use SimpleRouter\exceptions\RouteException;
use SimpleRouter\handlers\IRequestHandler;

abstract class AbstractRouteErrorHandler implements IRequestHandler {
    public RouteException $thrownError;

    public function setError(RouteException $e) {
        $this->thrownError = $e;
    }
}