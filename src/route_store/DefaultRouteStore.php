<?php

namespace SimpleRouter\route_store;

use SimpleRouter\exceptions\NotFoundException;

use SimpleRouter\route_comparator\RouteComparator;

/**
 * @class DefaultRouteStore
 * @version 1.0.0
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

    public function push($method, $template) {
        if (is_null($this->routes[$method])) {
            $this->routes[$method] = array();
        }
        array_push($this->routes[$method], $template);
        return true;
    }

    /**
     * @param string $path
     * @throws NotFoundException
     * @return Object(props : Array, handler : IRequestHandler)
     */
    public function match($path, $method) {
        $parsedPath = $this->parser->parse($path);
        $bucket = $this->routes[$method];
        if (isset($bucket)) {
            $propsAndHandler = $this->comparator->compare($parsedPath, $bucket);
            if ($propsAndHandler === null) {
                throw new NotFoundException("Cannot ".$method." ".$path);
            } else {
                return $propsAndHandler;
            }      
        } else {
            throw new NotFoundException("Cannot ".$method." ".$path);
        }
    }
}