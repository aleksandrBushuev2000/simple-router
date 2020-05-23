<?php

use PHPUnit\Framework\TestCase;

use SimpleRouter\route_store\DefaultRouteStore;
use SimpleRouter\template_parser\DefaultTemplateParser;

class RouteStoreTest extends TestCase {
    public function testHasMatchRoute() {
        try {
            $store = new DefaultRouteStore();
            $parser = new DefaultTemplateParser();

            $templates = [
                "/articles/{page? = 1}/",
                "/",
                "/about/",
                "/photos/",
                "geo/{latitude : double}/{longitude : double}"
            ];

            foreach ($templates as $key => $value) {
                $parsedTemplate = $parser->parseTemplate($value, null);
                $store->push("GET", $parsedTemplate);
            }

            $store->match("/articles/100", "GET");
        } catch (\Exception $e) {
            $this->fail("Unexpected Exception");
        }
    }

    public function testThrowNotFoundErrorIfNoneMatch() {
        try {
            $store = new DefaultRouteStore();
            $parser = new DefaultTemplateParser();

            $templates = [
                "/articles/{page? = 1}/",
                "/",
            ];

            foreach ($templates as $key => $value) {
                $parsedTemplate = $parser->parseTemplate($value, null);
                $store->push("GET", $parsedTemplate);
            }

            $store->match("/a/", "GET");
            $this->fail("Unexpected Exception");
        } catch (\Exception $e) {
          if (!($e instanceof \SimpleRouter\exceptions\NotFoundException)) {
              $this->fail("Unexpected Exception Type");
          }
        }
    }
}
