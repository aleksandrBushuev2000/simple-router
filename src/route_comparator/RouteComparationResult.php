<?php

namespace SimpleRouter\route_comparator;

use SimpleRouter\handlers\IRequestHandler;
use SimpleRouter\plugins\IRouterPlugin;

class RouteComparationResult {
    private int $lengthDelta;
    private array $params;
    private IRequestHandler $handler;
    private array $plugins;

    public function getLengthDelta() {
        return $this->lengthDelta;
    }

    public function setLengthDelta(int $lengthDelta) : RouteComparationResult {
        $this->lengthDelta = $lengthDelta;
        return $this;
    }

    public function getParams() {
        return $this->params;
    }

    public function setParams(array $params) : RouteComparationResult {
        $this->params = $params;
        return $this;
    }

    public function getHandler() : IRequestHandler {
        return $this->handler;
    }

    public function setHandler(IRequestHandler $handler) : RouteComparationResult {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @return array<IRouterPlugin>
    */
    public function getPlugins() : array {
        return $this->plugins;
    }

    public function setPlugins(array $plugins) : RouteComparationResult {
        $this->plugins = $plugins;
        return $this;
    }
}