<?php

use PHPUnit\Framework\TestCase;

use SimpleRouter\exceptions\NotFoundException;

use SimpleRouter\route_store\DefaultRouteStore;
use SimpleRouter\template_parser\DefaultTemplateParser;

/**
 * @class RouteStoreTest
 * @version 1.0.0
 * @author AleksandrBushuev
 * @description Test case for SimpleRouter/route_store/DefaultRouteStore
 */
class RouteStoreTest extends TestCase {

    private $routes = [
        "/articles/{page? = 1}/",
        "/",
        "/about/",
        "/photos/",
        "/geo/{latitude : double}/{longitude : double}",
        "/articles/{category? : integer = 0}/{id : integer}/{format? : string = json}"
    ];

    public function hasMatchRouteProvider() {
        $routes = $this->routes;
        return array(
            array($routes, "/articles/100"),
            array($routes, "/geo/17.43/42.43"),
            array($routes, "/"),
            array($routes, "/about"),
            array($routes, "/articles/100/rss/"),
        );
    }

    public function notFoundRoutesProvider() {
        $routes = $this->routes;
        return array(
            array($routes, "/articles/namespace/144/4245/32/100"),
            array($routes, "/geo/17.43/42.43/43/23"),
            array($routes, "/contacts"),
            array($routes, "/about/contacts"),
            array($routes, "/articles/cars/cars-11/json/"),
        );
    }

    /**
     * @dataProvider hasMatchRouteProvider
     * @param array<string> $routes : Array of routes
     * @param string $needle : Route to search
     */
    public function testHasMatchRoute($routes, $needle) {
        try {
            $store = new DefaultRouteStore();
            $parser = new DefaultTemplateParser();

            foreach ($routes as $key => $value) {
                $parsedTemplate = $parser->parseTemplate($value, null);
                $store->push("GET", $parsedTemplate);
            }

            $matchResult = $store->match($needle, "GET");
            $this->assertNotNull($matchResult);
        } catch (Exception $e) {
            $this->fail("Unexpected Exception");
        }
    }

    /**
     * @dataProvider notFoundRoutesProvider
     * @param array<string> $routes : Array of routes
     * @param string $needle : Route to search
     */
    public function testThrowNotFoundErrorIfNoneMatch($routes, $needle) {
        try {
            $store = new DefaultRouteStore();
            $parser = new DefaultTemplateParser();

            foreach ($routes as $key => $value) {
                $parsedTemplate = $parser->parseTemplate($value, null);
                $store->push("GET", $parsedTemplate);
            }

            $store->match($needle, "GET");
            $this->fail("Unexpected Exception");
        } catch (Exception $e) {
          if (!($e instanceof NotFoundException)) {
              $this->fail("Unexpected Exception Type");
          } else {
              $this->assertNotNull($e);
          }
        }
    }
}
