<?php


namespace SimpleRouter\route_comparator\strategy;


use SimpleRouter\route_comparator\RouteComparationResult;
use SimpleRouter\template\Template;

class StrictRoutePathComparator extends AbstractRoutePathComparator {

    function compare(array $parsedPath, Template $template): ?RouteComparationResult {
        $templateParts = $template->getParts();
        $params = array();
        for ($i = 0; $i < count($templateParts); $i++) {
            $comparationObject = $this->compareBucketAndTemplateParts(
                $parsedPath[$i],
                $templateParts[$i]
            );
            if ($comparationObject === null) {
                return null;
            } else {
                if ($comparationObject->getIsAlias() === true) {
                    $params[$comparationObject->getKey()] = $comparationObject->getValue();
                }
            }
        }
        $res = new RouteComparationResult();
        return $res->setLengthDelta(0)
            ->setParams($params)
            ->setHandler($template->getHandler())
            ->setPlugins($template->getPlugins());
    }
}