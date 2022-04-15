<?php

namespace SimpleRouter\handlers;

use SimpleRouter\request\Request;
use SimpleRouter\response\ResponseInterface;

interface IRequestHandler {

    /**
     * Handles request
     * @param Request $req - Request object
     * @return \SimpleRouter\response\ResponseInterface
     */
    public function handle(Request $req) : ResponseInterface;
}