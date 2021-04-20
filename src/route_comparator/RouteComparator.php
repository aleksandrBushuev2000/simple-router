<?php

namespace SimpleRouter\route_comparator;

use SimpleRouter\route_comparator\strategy\ComparationStrategyFactory;

/**
 * @class RouteComparator
 * @version 1.2.0
 * @author AleksandrBushuev
 * @description Provides ability to compare Route template with real path
 *
*/
class RouteComparator {

    private ComparationStrategyFactory $comparationFactory;

    function __construct() {
        $this->comparationFactory = new ComparationStrategyFactory();
    }

    private function compareWithTemplateAndReturnParams($parsedPath, $template) {
        $bucketLength = count($parsedPath);
        if ($bucketLength > $template->length) {
            return null;
        } else if ($bucketLength < $template->length - $template->optionalLength) {
            return null;
        } else {
            return $this->comparationFactory
                ->getComparator($parsedPath, $template)
                ->compare($parsedPath, $template);
        }
    }

    public function compare($parsedPath, $templates) : ?RouteComparationResult {
        $foundTemplateResults = array();
        for ($i = 0; $i < count($templates); $i++) {
            $template = $templates[$i];
            $propsAndHandler = $this->compareWithTemplateAndReturnParams($parsedPath, $template);
            if ($propsAndHandler !== null) {
                array_push($foundTemplateResults, $propsAndHandler);
            }
        }

        usort($foundTemplateResults, function(RouteComparationResult $a, RouteComparationResult $b) {
            return $a->getLengthDelta() - $b->getLengthDelta();
        });

        return count($foundTemplateResults) > 0 ? $foundTemplateResults[0] : null;
    }
}