<?php

namespace SimpleRouter\route_comparator;

use SimpleRouter\template\Template;
use SimpleRouter\template\TemplatePart;

/**
 * @class RouteComparator
 * @version 1.2.0
 * @author AleksandrBushuev
 * @description Provides ability to compare Route template with real path
 *
*/
class RouteComparator {

    private $DEFAULT_VALUES = [
        "integer" => 0,
        "double" => 0.0,
        "boolean" => false,
        "string" => "",
    ];

    private function isInteger($testString) {
        return strpos($testString, ".") === false && is_numeric($testString);   
    }

    private function isDouble($testString) {
        return strpos($testString, ".") !== false && is_numeric($testString);   
    }

    private function isBoolean($testString) {
        return $testString === "true" || $testString === "false";
    }

    private function isDatatype($testString, $type) {
        switch ($type) {
            case "integer" : return $this->isInteger($testString); 
            case "double" : return $this->isDouble($testString); 
            case "boolean" : return $this->isBoolean($testString); 
        }
        return true;
    }

    private function getValueByDatatype($str, $type) {
        if ($type == "integer") {
            return intval($str);
        } else if ($type == "double") {
            return doubleval($str);
        } else if ($type == "boolean") {
            return $str === "true" ? true : false; 
        }
        return $str;
    }

    private function compareBucketAndTemplateParts($bucketPart, TemplatePart $templatePart) {
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

    private function strictCompare(array $parsedPath, Template $template) {
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


    private function noOptionalCompare($parsedPath, Template $template) {
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

    private function findMatchTemplate($value, $templates, $delta, $index, $usedTemplates) {
        $foundTemplate = null;
        for ($i = $index; $i < $index + $delta; $i++) {
            if (!isset($usedTemplates[$i])) {
                $comparationObject = $this->compareBucketAndTemplateParts($value, $templates[$index]);
                if ($comparationObject !== null) {
                    return new class($comparationObject, $index) {
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


    private function someOptionalCompare($parsedPath, Template $template) {
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

    private function compareWithTemplateAndReturnParams($parsedPath, $template) {
        $bucketLength = count($parsedPath);
        if ($bucketLength > $template->length) {
            return null;
        } else if ($bucketLength < $template->length - $template->optionalLength) {
            return null;
        } else if ($bucketLength == $template->length) {
            return $this->strictCompare($parsedPath, $template);
        } else if ($bucketLength == $template->length - $template->optionalLength) {
            return $this->noOptionalCompare($parsedPath, $template);
        } else if ($bucketLength > $template->length - $template->optionalLength) {
            return $this->someOptionalCompare($parsedPath, $template);
        }
        return null;
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