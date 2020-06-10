<?php

use PHPUnit\Framework\TestCase;

use SimpleRouter\exceptions\NotFoundException;

use SimpleRouter\route_store\DefaultRouteStore;
use SimpleRouter\template_parser\DefaultTemplateParser;

use SimpleRouter\template_parser\exceptions\ParseException;

/**
 * @class RouteStoreTest
 * @version 2.0.0
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
            array($routes, "/geo/up/13.42"),
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
                $parsedTemplate = $parser->parseTemplate($value, new class implements \SimpleRouter\handlers\IRequestHandler {
                    public function handle(\SimpleRouter\request\Request $req){}
                }, []);
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
    public function testReturnNullNoneMatch($routes, $needle) {
        try {
            $store = new DefaultRouteStore();
            $parser = new DefaultTemplateParser();

            foreach ($routes as $key => $value) {
                $parsedTemplate = $parser->parseTemplate($value, new class implements \SimpleRouter\handlers\IRequestHandler {
                    public function handle(\SimpleRouter\request\Request $req){}
                }, []);
                $store->push("GET", $parsedTemplate);
            }

            $result = $store->match($needle, "GET");
            $this->assertEquals(null, $result);
        } catch (ParseException $e) {
            $this->fail();
        }

    }
}
