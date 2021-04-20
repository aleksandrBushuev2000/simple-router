<?php


namespace SimpleRouter\route_comparator\strategy;


use SimpleRouter\template\Template;

class ComparationStrategyFactory {
    private StrictRoutePathComparator $strictComparator;
    private DefaultRoutePathComparator $defaultComparator;
    private NoOptionalParametersRoutePathComparator $noOptionalComparator;

    function __construct() {
        $this->strictComparator = new StrictRoutePathComparator();
        $this->noOptionalComparator = new NoOptionalParametersRoutePathComparator();
        $this->defaultComparator = new DefaultRoutePathComparator();
    }

    /**
     * @param array $parsedPath
     * @param Template $template
     * @return DefaultRoutePathComparator
     */
    public function getComparator(array $parsedPath, Template $template): RouteComparationStrategyInterface {
        $bucketLength = count($parsedPath);
        if ($bucketLength == $template->length) {
            return $this->strictComparator;
        } else if ($bucketLength == $template->length - $template->optionalLength) {
            return $this->noOptionalComparator;
        } else if ($bucketLength > $template->length - $template->optionalLength) {
            return $this->defaultComparator;
        }
    }


}