<?php

namespace SimpleRouter\route_store;

use SimpleRouter\route_comparator\RouteComparationResult;
use SimpleRouter\route_comparator\RouteComparator;
use SimpleRouter\template\Template;

/**
 * @class DefaultRouteStore
 * @version 1.3.0
 * @author Aleksandr Bushuev
 * @description Provides ability to store routes and compare with real request path using RouteComparator
*/
class DefaultRouteStore implements IRouteStore {
    private $routes;
    private $parser;
    private $comparator;

    public function __construct() {

        $this->parser = new DefaultRouteStorePathParser();
        $this->comparator = new RouteComparator();

        $this->routes = array();
        $this->routes["GET"] = array();
        $this->routes["HEAD"] = array();
        $this->routes["POST"] = array();
        $this->routes["PUT"] = array();
        $this->routes["DELETE"] = array();
        $this->routes["OPTIONS"] = array();
    }

    public function push(string $method, Template $template) {
        if (is_null($this->routes[$method])) {
            $this->routes[$method] = array();
        }
        array_push($this->routes[$method], $template);
        return true;
    }

    /**
     * @param string $path
     * @param string $method
     * @return RouteComparationResult|null
     */
    public function match(string $path, string $method) : ?RouteComparationResult {
        $parsedPath = $this->parser->parse($path);
        $bucket = $this->routes[$method];
        if (isset($bucket)) {
            $comparationResult = $this->comparator->compare($parsedPath, $bucket);
            if ($comparationResult === null) {
                return null;
            } else {
                return $comparationResult;
            }      
        } else {
            return null;
        }
    }
}