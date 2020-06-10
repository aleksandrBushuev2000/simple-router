<?php

namespace SimpleRouter\template;

use SimpleRouter\template\exceptions\TemplatePartException;

/**
 * @class TemplatePart
 * @author Aleksandr Bushuev
 * @version 1.0.0
 * @description Implementation of route template part
 */

class TemplatePart {

    private string $name;
    private bool $isAlias;
    private bool $isOptional;
    private string $dataType;
    private string $initialValue;

    /**
     * @throws TemplatePartException
     * @param string $name
     * @return TemplatePart
    */
    public function setName(string $name) : TemplatePart {
        if ($name == "") {
            throw new TemplatePartException("Invalid name");
        }
        $this->name = $name;
        return $this;
    }

    public function setIsAlias(bool $isAlias) : TemplatePart {
        $this->isAlias = $isAlias;
        return $this;
    }

    public function setIsOptional(bool $isOptional) : TemplatePart {
        $this->isOptional = $isOptional;
        return $this;
    }

    public function setDatatype(string $datatype) : TemplatePart {
        $this->dataType = $datatype;
        return $this;
    }

    public function setInitValue(string $initValue) : TemplatePart {
        $this->initialValue = $initValue;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getIsAlias() : bool {
        return $this->isAlias;
    }

    public function getIsOptional() : bool {
        return $this->isOptional;
    }

    public function getDatatype() : string {
        return $this->dataType;
    }

    public function getInitValue() : string {
        return $this->initialValue;
    }
}