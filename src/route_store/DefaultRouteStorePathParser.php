<?php

namespace SimpleRouter\route_store;

/**
 * @class DefaultRouteStorePathParser
 * @author Aleksandr Bushuev
 * @version 1.3.0
 * @description Provides ability to parse route store paths
 */
class DefaultRouteStorePathParser {

    private $URL_SEPARATOR = "/";

    private function removeSpaces($parts) : array {
        return array_map(function($element) {
            return implode(explode(" ", $element));
        }, $parts);
    }

    private function filterEmpty($parts) : array {
        return array_filter($parts, function($element) {
            return $element != "";
        });
    } 

    public function parse($url) : array {
        if (strpos($url, "?") !== false) {
            $url = explode("?", $url)[0];
        }
        $templateParts = explode($this->URL_SEPARATOR, $url);
        $templateParts = $this->removeSpaces($templateParts);
        $templateParts = $this->filterEmpty($templateParts); 
        $templateParts = array_values($templateParts);
        return $templateParts;
    }
}