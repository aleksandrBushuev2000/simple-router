<?php

namespace SimpleRouter\route_comparator;

/**
 * @class RouteComparator
 * @version 1.0.0
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

    private function compareBucketAndTemplateParts($bucketPart, $templatePart) {
        $result = new RoutePartComparationResult();
        if ($templatePart->isAlias === true) {
            if ($this->isDatatype($bucketPart, $templatePart->dataType)) {
                $value = $this->getValueByDatatype($bucketPart, $templatePart->dataType);
                return $result->isAlias(true)->key($templatePart->name)->value($value)->equal(true);
            }
        } else if ($templatePart->name == $bucketPart) {
            return $result->key($templatePart->name)->isAlias(false)->equal(true);
        }
        return null;
    }

    private function strictCompare($parsedPath, $template) {
        $templateParts = $template->parts;
        $params = array();
        for ($i = 0; $i < count($templateParts); $i++) {
            $comparationObject = $this->compareBucketAndTemplateParts(
                $parsedPath[$i],
                $templateParts[$i]
            );
            if ($comparationObject === null) {
                return null;
            } else {
                if ($comparationObject->isAlias === true) {
                    $params[$comparationObject->key] = $comparationObject->value;
                }
            }
        }
        $res = new RouteComparationResult();
        return $res->lengthDelta(0)
                    ->params($params)
                    ->handler($template->handler);
    }


    private function noOptionalCompare($parsedPath, $template) {
        $templateParts = $template->parts;
        $params = array();
        $handler = $template->handler;

        $requiredIndexes = $template->requiredIndexes;
        $optionalIndexes = $template->optionalIndexes;

        for ($i = 0; $i < count($requiredIndexes); $i++) {
            $index = $requiredIndexes[$i];
            $part = $templateParts[$index];
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
            $part = $templateParts[$index];

            if ($part->initialValue) {
                $params[$part->name] = $part->initialValue;
            } else {
                $params[$part->name] = $this->DEFAULT_VALUES[$part->dataType];
            }
        }

        $res = new RouteComparationResult();
        return $res->lengthDelta(count($templateParts) - count($parsedPath))
            ->params($params)
            ->handler($handler);
    }

    private function findMatchTemplate($value, $templates, $delta, $index, $usedTemplates) {
        $foundTemplate = null;
        for ($i = $index; $i < $index + $delta; $i++) {
            if (!isset($usedTemplates[$i])) {
                $comparationObject = $this->compareBucketAndTemplateParts($value, $templates[$index]);
                if ($comparationObject != null) {
                    return new class($comparationObject, $index) {
                        public $comparationObject;
                        public $index;
    
                        public function __construct($comparationObject, $index) {
                            $this->comparationObject = $comparationObject;
                            $this->index = $index;
                        }
                    };
                }
            }
        }
        return null;
    }

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
                    $params[$comparationObject->key] = $comparationObject->value;
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
                if ($result == null) {
                    return null;
                } else {
                    $usedTemplates[$result->index] = true;
                    $props[$result->comparationObject->key] = $result->comparationObject->value;
                }
            }
            for ($i = 0; $i < count($templates); $i++) {
                if (!isset($usedTemplates[$i])) {
                    if ($templates[$i]->initialValue !== null) {
                        $props[$templates[$i]->name] = $templates[$i]->initialValue;
                    } else {
                        $props[$templates[$i]->name] = $this->DEFAULT_VALUES[$templates[$i]->dataType];
                    }
                }
            }
            return $props;
        }
    }

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
            $parsedPathPart = $parsedPath[$i];
            $comparationObject = $this->compareBucketAndTemplateParts($parsedPath[$i], $templatePart);
            if ($comparationObject !== null) {
                $comparationObject->foundAt($i);

                $optionalValues = array();
                for ($j = $lastFoundRequiredIndex + 1; $j < $i; $j++) {
                    array_push($optionalValues, $parsedPath[$j]);
                }

                $optionalTemplates = array();
              
                for ($j = $lastRequiredIndex + 1; $j < $maxIndex; $j++) {
                    if ($allTemplates[$j]->isOptional === true) {
                        array_push($optionalTemplates, $allTemplates[$j]);
                    } else {
                        return null;
                    }
                }

                $optionalParams = $this->compareOptionalValuesWithTemplates($optionalValues, $optionalTemplates);
                if ($optionalParams === null) {
                    return null;
                } else {
                    $comparationObject->optionalParams = $optionalParams;
                }

                return $comparationObject;
            }
        }
        
        return null;
    }


    private function someOptionalCompare($parsedPath, $template) {
        $templateParts = $template->parts;
        $params = array();
        $handler = $template->handler;

        $requiredIndexes = $template->requiredIndexes;

        $testOptionalIndexes = array();

        $lastFoundRequiredIndex = -1;
        $lastTemplateRequiredIndex = -1;

        $foundIndexes = array();

        $avaibleOptionalPositionsInTemplate = array();

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
                $params = array_merge($params, $comparationObject->optionalParams);
                if ($comparationObject->isAlias) {
                    $params[$comparationObject->key] = $comparationObject->value;
                }
                $lastFoundRequiredIndex = $comparationObject->foundAt;
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
        return $res->handler($handler)
            ->params($params)
            ->lengthDelta(count($templateParts) - count($parsedPath));
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

    public function compare($parsedPath, $templates) {
        $foundTemplateResults = array();
        for ($i = 0; $i < count($templates); $i++) {
            $template = $templates[$i];
            $propsAndHandler = $this->compareWithTemplateAndReturnParams($parsedPath, $template);
            if ($propsAndHandler !== null) {
                array_push($foundTemplateResults, $propsAndHandler);
            }
        }

        usort($foundTemplateResults, function($a, $b) {
            return $a->lengthDelta - $b->lengthDelta;
        });

        return count($foundTemplateResults) > 0 ? $foundTemplateResults[0] : null;
    }
}