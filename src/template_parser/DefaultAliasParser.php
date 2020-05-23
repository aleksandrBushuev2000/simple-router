<?php

namespace SimpleRouter\template_parser;

use SimpleRouter\template\TemplatePart;
use SimpleRouter\template_parser\exceptions\ParseException;

/**
 * @class DefaultAliasParser
 * @version 1.0.0
 * @author Aleksandr Bushuev
 */
class DefaultAliasParser {

    private $DATA_TYPES = [
        "integer" => true,
        "double" => true,
        "boolean" => true,
        "string" => true,
    ];

    private $IGNORE_LIST = [
        " " => true,
        "\r" => true,
        "\n" => true,
    ];

    private $ERROR_STOP_LIST = [
        "datatype" => [
            "{" => true,
            ":" => true,
            "?" => true,
        ],
        "default_value" => [
            "{" => true,
            ":" => true,
            "?" => true
        ],
        "name" => [
            "{" => true
        ]
    ];

    private $STOP_LIST = [
        "datatype" => [
            "=" => true,
            "}" => true,
        ],
        "default_value" => [
           "}" => true,
        ],
        "name" => [
            "}" => true,
            "?" => true,
            ":" => true
        ]
    ];

    /**
     * @throws ParseException
     * @param string $alias
     * @param string $type
     * @param int $from
     * @return string
     */
    private function getSubstringUntilFirstStopSymbol($alias, $type, $from = 0) {
        $stopSymbols = $this->STOP_LIST[$type];
        $errorStopSymbols = $this->ERROR_STOP_LIST[$type];

        $selectedString = "";

        $cursor = $from;

        while (!isset($stopSymbols[$alias[$cursor]])) {
            if (isset($errorStopSymbols[$alias[$cursor]])) {
                throw new ParseException("Invalid alias expression");
            }
            if (!isset($this->IGNORE_LIST[$alias[$cursor]])) {
                $selectedString = $selectedString.$alias[$cursor];
            }
            $cursor++;
            if ($cursor >= (strlen($alias))) {
                throw new ParseException("Invalid alias expression");
            }
        }

        if (strlen($selectedString) == 0) {
            throw new ParseException("Invalid alias expression");
        }

        return $selectedString;
    }

    /**
     * @throws ParseException
     * @param string $alias
     * @param string $type
     * @param string $sectionSeparator
     * @return string | null
     */
    private function getSection($alias, $type, $sectionSeparator) {
        $symbolPosition = strpos($alias, $sectionSeparator);
        if ($symbolPosition !== false) {
            $section = $this->getSubstringUntilFirstStopSymbol($alias, $type, $symbolPosition + 1);
            return new class($section, $symbolPosition) {
                public $section;
                public $position;

                public function __construct($section, $position) {
                    $this->section = $section;
                    $this->position = $position;
                }
            };
        }
        return null;
    }

    /**
     * @throws ParseException
     * @param {section : string, position : string} $nameObject
     * @return string
     */
    private function getDatatype($datatypeObject) {
        if (is_null($datatypeObject) || strlen($datatypeObject->section) == 0) {
            return "string";
        } else {
            $datatype = $datatypeObject->section;
            if ($datatype != null) {
                $datatype = strtolower($datatype);
                if (isset($this->DATA_TYPES[$datatype])) {
                    return $datatype;
                }
            }
            throw new ParseException("Invalid datatype");
        }
    }

    /**
     * @param {section : string, position : string} $nameObject
     * @return string
     */
    private function getDefaultValue($defaultValueObject) {
        if (is_null($defaultValueObject)) {
            return null;
        } else {
            return $defaultValueObject->section;
        }
    }

    /**
     * @throws ParseException
     * @param {section : string, position : string} $nameObject
     * @return string
     */
    private function getName($nameObject) {
        if (is_null($nameObject) || strlen($nameObject->section) == 0) {
            throw new ParseException("Invalid name section");
        } else {
            return $nameObject->section;
        }
    }

    /**
     * @param string $value
     * @param string $datatype
     * @return string
     */
    private function resolveDefaultValue($value, $datatype) {
        $datatype = strtolower($datatype);
        if (!is_null($value) && $datatype != "string") {
            switch($datatype) {
                case "integer" : return intval($value);
                case "double" : return doubleval($value);
                case "boolean" : return boolval($value);
            }
        }
        return $value;
    }

    /**
     * @throws ParseException
     * @param string @alias
     * @return TemplatePart
    */
    public function parse($alias) {
        $datatype = $this->getDatatype($this->getSection($alias, "datatype", ":"));
        $defaultValue = $this->getDefaultValue($this->getSection($alias, "default_value", "="));
        $name = $this->getName($this->getSection($alias, "name", "{"));
        
        $isOptional = strpos($alias, "?") === false ? false : true;
        $defaultValue = $this->resolveDefaultValue($defaultValue, $datatype);

        $res = new TemplatePart();
        return $res->name($name)->isAlias(true)->isOptional($isOptional)->dataType($datatype)->initialValue($defaultValue);
    }
}