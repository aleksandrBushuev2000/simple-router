<?php

namespace SimpleRouter\template_parser;

/**
 * @class DefaultAliasParserSectionTransporter
 * @version 1.0.0
 * @author Aleksandr Bushuev
*/
class DefaultAliasParserSectionTransporter {
    public string $section;
    public int $position;

    public function __construct(string $section, int $position) {
        $this->section = $section;
        $this->position = $position;
    }
}