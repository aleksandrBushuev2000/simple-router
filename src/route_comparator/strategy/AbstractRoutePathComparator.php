<?php


namespace SimpleRouter\route_comparator\strategy;


use SimpleRouter\route_comparator\RouteComparationResult;
use SimpleRouter\route_comparator\RoutePartComparationResult;
use SimpleRouter\template\Template;
use SimpleRouter\template\TemplatePart;

abstract class AbstractRoutePathComparator implements RouteComparationStrategyInterface {
    protected $DEFAULT_VALUES = [
        "integer" => 0,
        "double" => 0.0,
        "boolean" => false,
        "string" => "",
    ];

    protected function isInteger($testString) {
        return strpos($testString, ".") === false && is_numeric($testString);
    }

    protected function isDouble($testString) {
        return strpos($testString, ".") !== false && is_numeric($testString);
    }

    protected function isBoolean($testString) {
        return $testString === "true" || $testString === "false";
    }

    protected function isDatatype($testString, $type) {
        switch ($type) {
            case "integer" : return $this->isInteger($testString);
            case "double" : return $this->isDouble($testString);
            case "boolean" : return $this->isBoolean($testString);
        }
        return true;
    }

    protected function getValueByDatatype($str, $type) {
        if ($type == "integer") {
            return intval($str);
        } else if ($type == "double") {
            return doubleval($str);
        } else if ($type == "boolean") {
            return $str === "true";
        }
        return $str;
    }

    protected function compareBucketAndTemplateParts($bucketPart, TemplatePart $templatePart) {
        $result = new RoutePartComparationResult();
        if ($templatePart->getIsAlias() === true) {
            if ($this->isDatatype($bucketPart, $templatePart->getDatatype())) {
                $value = $this->getValueByDatatype($bucketPart, $templatePart->getDatatype());
                return $result->setIsAlias(true)
                    ->setKey($templatePart->getName())
                    ->setValue($value)
                    ->setIsEqual(true);
            }
        } else if ($templatePart->getName() == $bucketPart) {
            return $result->setKey($templatePart->getName())
                ->setIsAlias(false)
                ->setIsEqual(true);
        }
        return null;
    }

    abstract function compare(array $parsedPath, Template $template): ?RouteComparationResult;
}