<?php

namespace SimpleRouter\route_store;

use SimpleRouter\template\Template;

/**
 * @interface IRouteStore
 * @author Aleksandr Bushuev
 * @version 1.3.0
 * @description Interface for abstract Route Store
 */
interface IRouteStore {
    public function push(string $method, Template $template);
    public function match(string $path, string $method);
}