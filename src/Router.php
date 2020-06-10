<?php

namespace SimpleRouter;

use SimpleRouter\exceptions\RouteException;
use SimpleRouter\handlers\error_handler\AbstractRouteErrorHandler;
use SimpleRouter\handlers\error_handler\RouteExceptionHandler;

use SimpleRouter\handlers\IRequestHandler;
use SimpleRouter\plugins\IRouterPlugin;
use SimpleRouter\request\Request;
use SimpleRouter\route_store\IRouteStore;
use SimpleRouter\template_parser\DefaultTemplateParser;

use SimpleRouter\route_store\DefaultRouteStore;
use SimpleRouter\template_parser\exceptions\ParseException;

use SimpleRouter\template_parser\ITemplateParser;
use Throwable;

/**
 * @class Router
 * @version 2.0.0
 * @author Aleksandr Bushuev
 * @license MIT
 * @description Main part of this library.
 * Router supports all http methods with primitive data types, optional route parts and default values.
 * You can override default error handler
 * Router is a Singleton, so, you MUST call static method Router::getInstance() to have ability to use router.
 * After definition of all paths you MUST call handle() method;
 *
 * Note! Router will be ignore default values for non - optional route parts.
 * Note! All handlers MUST implements IRequestHandler interface (@see ./handlers/IRequestHandler.php)
 *
 * @example
 * class ExampleHandler implements IRequestHandler {
 *      public function handler(Request $req) {
            var_dump($req);
 *      }
 * }
 *
 * $router = Router::getInstance();
 *
 * $router->get("/articles/{category?}/{id : integer}/{format? = html}", new ExampleHandler());
 *
 * $router->handle();
*/
class Router {

    private ITemplateParser $templateParser;
    private IRouteStore $store;

    private static Router $Router;

    private AbstractRouteErrorHandler $errorHandler;

    private function __construct() {
        $this->errorHandler = new RouteExceptionHandler();
        $this->templateParser = new DefaultTemplateParser();
        $this->store = new DefaultRouteStore();
    }

    /**
     * @description Sets Error handler
     * @param AbstractRouteErrorHandler $handler
     */
    public function setErrorHandler(AbstractRouteErrorHandler $handler) {
        $this->errorHandler = $handler;
    }

    /**
     * @description Returns an instance of router
     * @return Router
     */
    public static function getInstance() : Router {
        if (!isset(self::$Router)) {
            self::$Router = new self();
        }
        return self::$Router;
    }

    /**
     * @throws ParseException
     * @param string $path sth like this: "/articles/{category?}/{id : integer}/{format? = html}"
     * @param IRequestHandler $handler
     * @param array<IRouterPlugin> $plugins
     */
    public function get(string $path, IRequestHandler $handler, array $plugins = []) {
        $this->request("GET", $path, $handler, $plugins);
    }

    /**
     * @throws ParseException
     * @param string $path sth like this: "/articles/{category?}/{id : integer}/{format? = html}"
     * @param IRequestHandler $handler
     * @param array<IRouterPlugin> $plugins
     */
    public function post(string $path, IRequestHandler $handler, array $plugins = []) {
        $this->request("POST", $path, $handler, $plugins);
    }

    /**
     * @throws ParseException
     * @param string $path sth like this: "/articles/{category?}/{id : integer}/{format? = html}"
     * @param IRequestHandler $handler
     * @param array<IRouterPlugin> $plugins
     */
    public function put(string $path, IRequestHandler $handler, array $plugins = []) {
        $this->request("PUT", $path, $handler, $plugins);
    }

    /**
     * @throws ParseException
     * @param string $path sth like this: "/articles/{category?}/{id : integer}/{format? = html}"
     * @param IRequestHandler $handler
     * @param array<IRouterPlugin> $plugins
     */
    public function delete(string $path, IRequestHandler $handler, array $plugins = []) {
        $this->request("DELETE", $path, $handler, $plugins);
    }

    /**
     * @throws ParseException
     * @param string $path sth like this: "/articles/{category?}/{id : integer}/{format? = html}"
     * @param IRequestHandler $handler
     * @param array<IRouterPlugin> $plugins
     */
    public function patch(string $path, IRequestHandler $handler, array $plugins = []) {
        $this->request("PATCH", $path, $handler, $plugins);
    }

    /**
     * @throws ParseException
     * @param string $method (GET | POST | PUT | DELETE | etc...)
     * @param string $path sth like this: "/articles/{category?}/{id : integer}/{format? = html}"
     * @param IRequestHandler $handler
     * @param array<IRouterPlugin> $plugins
     */
    public function request($method, $path, $handler, array $plugins = []) {
        $template = $this->templateParser->parseTemplate($path, $handler, $plugins);
        $this->store->push($method, $template);
    }

    private function handleException(Request $req, Throwable $e) {
        if ($e instanceof RouteException) {
            $this->errorHandler->setError($e);
            $this->errorHandler->handle($req);
        } else {
            $error = new RouteException("Internal Serve Error", 500);
            $this->errorHandler->setError($error);
            $this->errorHandler->handle($req);
        }
    }

    private function executePlugins(array $plugins, Request $req) : bool {
        try {
            foreach ($plugins as $index => $plugin) {
                /**
                 * @var IRouterPlugin $plugin
                 */
                $plugin->execute($req);
            }
            return true;
        } catch (Throwable $e) {
            $this->handleException($req, $e);
            return false;
        }
    }

    private function handleRequest(IRequestHandler $handler, Request $req) {
        try {
            $handler->handle($req);
        } catch (Throwable $e) {
            $this->handleException($req, $e);
        }
    }

    /**
     * @description Handles request. You must call this method after definition of all route templates
    */
    public function handle() {
        try {
            $path = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            $comparationResult = $this->store->match($path, $method);
            if ($comparationResult === null) {
                throw new RouteException("Cannot ".$method." ".$path, 404);
            } else {
                $req = Request::create($comparationResult->getParams());
                $plugins = $comparationResult->getPlugins();

                if ($this->executePlugins($plugins, $req)) {
                    $handler = $comparationResult->getHandler();
                    $this->handleRequest($handler, $req);
                }
            }
        } catch (Throwable $e) {
            $this->handleException(Request::create([]), $e);
        }
    }
}
