<?php

namespace SimpleRouter\template;

use SimpleRouter\handlers\IRequestHandler;
use SimpleRouter\plugins\IRouterPlugin;

/**
 * @class Template
 * @author Aleksandr Bushuev
 * @version 2.0.0
 * @description Implementation of route template
*/
class Template {

    private int $length;
    private array $parts;
    private int $optionalLength;

    private array $plugins;
    private IRequestHandler $handler;

    private $requiredIndexes = array();
    private $optionalIndexes = array();

    public function __construct(IRequestHandler $handler, array $plugins) {
        $this->handler = $handler;
        $this->length = 0;
        $this->optionalLength = 0;
        $this->plugins = $plugins;
        $this->parts = array();
    }

    public function getLength() : int {
        return $this->length;
    }

    public function getParts() : array {
        return $this->parts;
    }

    public function getOptionalLength() : int {
        return $this->optionalLength;
    }

    public function getOptionalIndexes() {
        return $this->$this->optionalIndexes;
    }

    public function getHandler() : IRequestHandler {
        return $this->handler;
    }

    /**
     * @return array<IRouterPlugin>
    */
    public function getPlugins() : array {
        return $this->plugins;
    }

    public function getRequiredIndexes() {
        return $this->requiredIndexes;
    }

    public function __get($key) {
        if (isset($this->$key)) {
            return $this->$key;
        }
        return null;
    }


    public function push(TemplatePart $part) : bool {
        if ($part != null) {
            array_push($this->parts, $part);
            $this->length++;
            if ($part->getIsOptional()) {
                array_push($this->optionalIndexes, $this->length - 1);
                $this->optionalLength++;
            } else {
                array_push($this->requiredIndexes, $this->length - 1);
            }
            return true;
        }
        return false;
    }
}