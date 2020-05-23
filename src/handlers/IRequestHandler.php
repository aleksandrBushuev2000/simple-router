<?php

namespace SimpleRouter\handlers;

use SimpleRouter\request\Request;

interface IRequestHandler {

    /**
     * Handles request
     * @param Request $req - Request object
     * @return void
     */
    public function handle(Request $req);
}