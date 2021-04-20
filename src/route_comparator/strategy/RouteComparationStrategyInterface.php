<?php


namespace SimpleRouter\route_comparator\strategy;


use SimpleRouter\route_comparator\RouteComparationResult;
use SimpleRouter\template\Template;

interface RouteComparationStrategyInterface {
    function compare(array $parsedPath, Template $template) : ?RouteComparationResult;
}