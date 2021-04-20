<?php


namespace SimpleRouter\route_comparator\strategy;


use SimpleRouter\route_comparator\RouteComparationResult;
use SimpleRouter\route_comparator\RoutePartComparationResult;
use SimpleRouter\template\Template;
use SimpleRouter\template\TemplatePart;

class DefaultRoutePathComparator extends AbstractRoutePathComparator {

    private function findMatchTemplate($value, $templates, $delta, $index, $usedTemplates) {
        $foundTemplate = null;
        for ($i = $index; $i < $index + $delta + 1; $i++) {
            if (!isset($usedTemplates[$i])) {
                $comparationObject = $this->compareBucketAndTemplateParts($value, $templates[$i]);
                if ($comparationObject !== null) {
                    return new class($comparationObject, $i) {
                        public RoutePartComparationResult $comparationObject;
                        public int $index;

                        public function __construct(RoutePartComparationResult $comparationObject, int $index) {
                            $this->comparationObject = $comparationObject;
                            $this->index = $index;
                        }

                        public function getIndex() : int {
                            return $this->index;
                        }

                        public function getComparationObject() : RoutePartComparationResult {
                            return $this->comparationObject;
                        }
                    };
                }
            }
        }
        return null;
    }

    /**
     * @param string[] $values
     * @param TemplatePart[] $templates
     * @return array|null
     */
    private function compareOptionalValuesWithTemplates($values, $templates) {
        $params = array();
        if (count($values) > count($templates)) {
            return null;
        } else if (count($values) == count($templates)) {
            if (count($values) == 0) {
                return array();
            }
            for ($i = 0; $i < count($values); $i++) {
                $comparationObject = $this->compareBucketAndTemplateParts($values[$i], $templates[$i]);
                if ($comparationObject == null) {
                    return null;
                } else {
                    $params[$comparationObject->getKey()] = $comparationObject->getValue();
                }
                return $params;
            }
        } else {
            $props = array();
            $usedTemplates = array();
            $delta = count($templates) - count($values);
            for ($i = 0; $i < count($values); $i++) {
                $value = $values[$i];
                $result = $this->findMatchTemplate($value, $templates, $delta, $i, $usedTemplates);
                if ($result === null) {
                    return null;
                } else {
                    /**
                     * @var RoutePartComparationResult $comparationObject
                     */
                    $comparationObject = $result->getComparationObject();
                    $usedTemplates[$result->getIndex()] = true;
                    $props[$comparationObject->getKey()] = $comparationObject->getValue();
                }
            }

            for ($i = 0; $i < count($templates); $i++) {
                if (!isset($usedTemplates[$i])) {
                    if ($templates[$i]->getInitValue() !== null) {
                        $props[$templates[$i]->getName()] = $templates[$i]->getInitValue();
                    } else {
                        $props[$templates[$i]->getName()] = $this->DEFAULT_VALUES[$templates[$i]->getDatatype()];
                    }
                }
            }
            return $props;
        }
        return null;
    }

    /**
     * @param string[] $parsedPath
     * @param TemplatePart $templatePart
     * @param int $maxIndex
     * @param int $lastFoundRequiredIndex
     * @param TemplatePart[] $allTemplates
     * @param int $lastRequiredIndex
     * @return RoutePartComparationResult|null
     */
    private function findRequiredPart(
        $parsedPath,
        $templatePart,
        $maxIndex,
        $lastFoundRequiredIndex,
        $allTemplates,
        $lastRequiredIndex
    ) {

        $usedMaxIndex = $maxIndex;

        if ($usedMaxIndex > count($parsedPath) - 1) {
            $usedMaxIndex = count($parsedPath) - 1;
        }
        for ($i = $usedMaxIndex; $i > $lastFoundRequiredIndex; $i--) {
            $comparationObject = $this->compareBucketAndTemplateParts($parsedPath[$i], $templatePart);
            if ($comparationObject !== null) {
                $comparationObject->setFoundAt($i);

                $optionalValues = array();
                for ($j = $lastFoundRequiredIndex + 1; $j < $i; $j++) {
                    array_push($optionalValues, $parsedPath[$j]);
                }

                $optionalTemplates = array();


                for ($j = $lastRequiredIndex + 1; $j < $maxIndex; $j++) {
                    if ($allTemplates[$j]->getIsOptional() === true) {
                        array_push($optionalTemplates, $allTemplates[$j]);
                    } else {
                        return null;
                    }
                }

                $optionalParams = $this->compareOptionalValuesWithTemplates($optionalValues, $optionalTemplates);
                if ($optionalParams === null) {
                    return null;
                } else {
                    $comparationObject->setOptionalParams($optionalParams);
                }

                return $comparationObject;
            }
        }

        return null;
    }

    function compare(array $parsedPath, Template $template): ?RouteComparationResult {
        $templateParts = $template->getParts();
        $params = array();
        $handler = $template->getHandler();
        $plugins = $template->getPlugins();

        $requiredIndexes = $template->getRequiredIndexes();

        $lastFoundRequiredIndex = -1;
        $lastTemplateRequiredIndex = -1;

        for($i = 0; $i < count($requiredIndexes); $i++) {
            $index = $requiredIndexes[$i];
            $templatePart = $templateParts[$index];
            $comparationObject = $this->findRequiredPart(
                $parsedPath,
                $templatePart,
                $index,
                $lastFoundRequiredIndex,
                $templateParts,
                $lastTemplateRequiredIndex
            );
            if ($comparationObject == null) {
                return null;
            } else {
                $params = array_merge($params, $comparationObject->getOptionalParams());
                if ($comparationObject->getIsAlias()) {
                    $params[$comparationObject->getKey()] = $comparationObject->getValue();
                }
                $lastFoundRequiredIndex = $comparationObject->getFoundAt();
                $lastTemplateRequiredIndex = $index;
            }
        }

        $optionalParts = array();
        for ($i = $lastFoundRequiredIndex + 1; $i < count($parsedPath); $i++) {
            array_push($optionalParts, $parsedPath[$i]);
        }

        $optionalTemplates = array();
        for ($i = $lastTemplateRequiredIndex + 1; $i < count($templateParts); $i++) {
            array_push($optionalTemplates, $templateParts[$i]);
        }

        $tailOptionalParams = $this->compareOptionalValuesWithTemplates($optionalParts, $optionalTemplates);
        if ($tailOptionalParams) {
            $params = array_merge($params, $tailOptionalParams);
        }

        $res = new RouteComparationResult();
        return $res->setHandler($handler)
            ->setParams($params)
            ->setPlugins($plugins)
            ->setLengthDelta(count($templateParts) - count($parsedPath));
    }
}