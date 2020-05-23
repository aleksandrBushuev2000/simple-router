<?php

namespace SimpleRouter\template_parser;

use SimpleRouter\handlers\IRequestHandler;
use SimpleRouter\template\Template;

/**
 * @interface ITemplateParser
 * @author Aleksandr Bushuev
 * @version 1.0.0
 * @description Interface for All template Parsers
 */
interface ITemplateParser {
    /**
     * Parse template
     * @param string $path - request path (template, f.e. /members/{memberId}/{sectionName}/)
     * @param IRequestHandler $handler - request handler with "handle" method
     * @return Template
     */
    public function parseTemplate(string $path, IRequestHandler $handler);
}