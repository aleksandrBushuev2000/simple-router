<?php

namespace SimpleRouter\route_store;

/**
 * @interface IRouteStore
 * @author Aleksandr Bushuev
 * @version 1.0.0
 * @description Interface for abstract Route Store
 */
interface IRouteStore {
    public function push($method, $template);
    public function match($path, $method);
}