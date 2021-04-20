<?php


namespace SimpleRouter\route_comparator\strategy;


use SimpleRouter\route_comparator\RouteComparationResult;
use SimpleRouter\template\Template;
use SimpleRouter\template\TemplatePart;

class NoOptionalParametersRoutePathComparator extends AbstractRoutePathComparator {

    function compare(array $parsedPath, Template $template): ?RouteComparationResult {
        /**
         * @var TemplatePart[] $templateParts
         */
        $templateParts = $template->getParts();
        $params = array();
        $handler = $template->getHandler();
        $plugins = $template->getPlugins();

        $requiredIndexes = $template->getRequiredIndexes();
        $optionalIndexes = $template->getOptionalIndexes();

        for ($i = 0; $i < count($requiredIndexes); $i++) {
            $index = $requiredIndexes[$i];
            $comparationObject = $this->compareBucketAndTemplateParts(
                $parsedPath[$i],
                $templateParts[$index]
            );
            if ($comparationObject === null) {
                return null;
            }

            if ($comparationObject->getIsAlias() === true) {
                $params[$comparationObject->getKey()] = $comparationObject->getValue();
            }
        }

        for ($i = 0; $i < count($optionalIndexes); $i++) {
            $index = $optionalIndexes[$i];
            /**
             * @var TemplatePart $part
             */
            $part = $templateParts[$index];

            if ($part->getInitValue()) {
                $params[$part->getName()] = $part->getInitValue();
            } else {
                $params[$part->getName()] = $this->DEFAULT_VALUES[$part->getDatatype()];
            }
        }

        $res = new RouteComparationResult();
        return $res->setLengthDelta(count($templateParts) - count($parsedPath))
            ->setParams($params)
            ->setPlugins($plugins)
            ->setHandler($handler);
    }
}