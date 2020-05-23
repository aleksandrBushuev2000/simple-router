<?php

namespace SimpleRouter;

use SimpleRouter\exceptions\BadRequestException;
use SimpleRouter\exceptions\NotFoundException;
use SimpleRouter\exceptions\UndefinedHttpMethodException;

use SimpleRouter\handlers\default_handlers\BadRequestHandler;
use SimpleRouter\handlers\default_handlers\NotFoundHandler;
use SimpleRouter\handlers\default_handlers\InternalServeErrorHandler;

use SimpleRouter\handlers\IRequestHandler;
use SimpleRouter\request_parser\DefaultRequestParser;
use SimpleRouter\template_parser\DefaultTemplateParser;

use SimpleRouter\route_store\DefaultRouteStore;
use SimpleRouter\template_parser\exceptions\ParseException;

/**
 * @class Router
 * @version 1.0.0
 * @author Aleksandr Bushuev
 * @description Main part of this library.
 * Router supports all http methods with primitive data types, optional route parts and default values.
 * You can override default error (404, 500) handlers.
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
 * $router->get("/articles/{category?}/{id : integer}/{format? = html}", new ExampleHandler);
 *
 * $router->handle();
*/
class Router {

    private $templateParser;
    private $requestParser;
    private $store;

    private static $Router = null;

    private $handlers = [
        "404" => null,
        "500" => null,
        "400" => null,
    ];

    private $AVAIBLE_HTTP_METHODS = [
        "GET" => true,
        "HEAD" => true,
        "POST" => true,
        "PUT" => true,
        "DELETE" => true,
        "PATCH" => true,
        "TRACE" => true,
        "CONNECT" => true,
        "OPTIONS" => true,
    ];

    private function __construct() {
        $this->templateParser = new DefaultTemplateParser();
        $this->requestParser = new DefaultRequestParser();
        $this->store = new DefaultRouteStore();
    }

    /**
     * @description Sets 404 Error handler (if route template not found)
     * @param IRequestHandler $handler
     */
    public function set404Handler(IRequestHandler $handler) {
        $this->handlers["404"] = $handler;
    }

    /**
     * @description Sets 500 Error handler (Internal Serve Error)
     * @param IRequestHandler $handler
     */
    public function set500Handler(IRequestHandler $handler) {
        $this->handlers["500"] = $handler;
    }

    /**
     * @description Sets 400 Error handler (Bad Request)
     * @param IRequestHandler $handler
     */
    public function set400Handler(IRequestHandler $handler) {
        $this->handlers["400"] = $handler;
    }

    /**
     * @description Returns an instance of router
     * @return Router
     */
    public static function getInstance() {
        if (self::$Router == null) {
            self::$Router = new self();
        }
        return self::$Router;
    }

    /**
     * @throws UndefinedHttpMethodException
     * @throws ParseException
     * @param string $method - valid http method name
     * @param $arg - route path and route handler
     */
    public function __call($method, $arg) {
        $httpRequestMethod = strtoupper($method);
        if (isset($this->AVAIBLE_HTTP_METHODS[$httpRequestMethod])) {
            $path = $arg[0];
            $handler = $arg[1];
            $this->request($httpRequestMethod, $path, $handler);
        } else {
            throw new UndefinedHttpMethodException("Undefined http method: ".$httpRequestMethod);
        }
    }

    /**
     * @throws ParseException
     * @param string $method (GET | POST | PUT | DELETE | etc...)
     * @param string $path sth like this: "/articles/{category?}/{id : integer}/{format? = html}"
     * @param IRequestHandler @handler
     */
    public function request($method, $path, $handler) {
        $template = $this->templateParser->parseTemplate($path, $handler);
        $this->store->push($method, $template);
    }

    /**
     * @throws BadRequestException
     */
    private function parseRequest() {
        try {
            return $this->requestParser->parse();
        } catch(\Exception $e) {
            throw new BadRequestException("Bad request");
        } 
    }

    private function requireErrorHandlers() {
        if (!isset($this->handlers["400"])) {
            $this->handlers["400"] = new BadRequestHandler();
        }
        if (!isset($this->handlers["500"])) {
            $this->handlers["500"] = new InternalServeErrorHandler();
        }
        if (!isset($this->handlers["404"])) {
            $this->handlers["404"] = new NotFoundHandler();
        }
    }

    /**
     * @description Handles request. You must call this method after definition of all route templates
    */
    public function handle() {
        try {
            $this->requireErrorHandlers();
            $path = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            $req = $this->parseRequest();
            $handlerAndParams = $this->store->match($path, $method);
            $req->params = $handlerAndParams->params;
            $handlerAndParams->handler->handle($req);
        } catch(BadRequestException $e) {
            $this->handlers["400"]->handle(null);
        } catch(NotFoundException $e) {
            $this->handlers["404"]->handle(null);
        } catch(\Throwable $e) {
            $this->handlers["500"]->handle(null);
        }
    }
}
