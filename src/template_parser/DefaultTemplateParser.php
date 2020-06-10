<?php

namespace SimpleRouter\template_parser;

use SimpleRouter\handlers\IRequestHandler;
use SimpleRouter\plugins\IRouterPlugin;
use SimpleRouter\template\exceptions\TemplatePartException;
use SimpleRouter\template\Template;
use SimpleRouter\template\TemplatePart;
use SimpleRouter\template_parser\exceptions\ParseException;

use Throwable;

/**
 * @class DefaultTemplateParser
 * @author Aleksandr Bushuev
 * @version 1.0.0
 * @description Implementation of route template parser
 */
class DefaultTemplateParser implements ITemplateParser {
    private $URL_SEPARATOR = "/";
    private $aliasParser = null;

    private function hasAlias($part) {
        return strpos($part, "{") == 0 && strrpos($part, "}") == strlen($part) - 1;
    }

    /**
     * @throws ParseException
     * @throws TemplatePartException
     * @param string $part
     * @return TemplatePart
     */
    private function getTemplatePart($part) : TemplatePart {
        if ($this->hasAlias($part)) {
            return $this->aliasParser->parse($part);
        } else {
            $res = new TemplatePart();
            return $res->setName($part)
                ->setIsAlias(false)
                ->setIsOptional(false)
                ->setDatatype("")
                ->setInitValue("");
        }
    }

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

    private function urlToArray($urlTemplate) : array {
        $templateParts = explode($this->URL_SEPARATOR, $urlTemplate);
        $templateParts = $this->removeSpaces($templateParts);
        $templateParts = $this->filterEmpty($templateParts); 
        $templateParts = array_values($templateParts);
        return $templateParts;
    }


    /**
     * @throws ParseException
     * @param string $urlTemplate
     * @param IRequestHandler $handler
     * @param array<IRouterPlugin> $plugins
     * @return Template
     */
    public function parseTemplate(string $urlTemplate, IRequestHandler $handler, array $plugins) : Template {
        try {
            $templateParts = $this->urlToArray($urlTemplate);
            $template = new Template($handler, $plugins);
            for ($i = 0; $i < count($templateParts); $i++) {
                $templatePart = $this->getTemplatePart($templateParts[$i]);
                $template->push($templatePart);
            }

            return $template;
        } catch(Throwable $e) {
            throw new ParseException($e->getMessage());
        }
    }

    public function __construct() {
        $this->aliasParser = new DefaultAliasParser();
    }
}