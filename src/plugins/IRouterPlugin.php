<?php

namespace SimpleRouter\plugins;

use SimpleRouter\request\Request;

interface IRouterPlugin {
    public function execute(Request $req) : Request;
}