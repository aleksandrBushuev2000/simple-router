<?php

namespace SimpleRouter\plugins;

use SimpleRouter\exceptions\RouteException;
use SimpleRouter\request\Request;

use Throwable;

/**
 * @class JsonBodyParserPlugin
 * @author AleksandrBushuev
 * @version 1.0.0
 * @description
 * A plugin that provides ability to parse requests with JSON body
 * It injects into request variable 'body' with parsed JSON input
 *
 * @example
 * class ExampleHandler implements IRequestHandler {
 *      public function handler(Request $req) {
            var_dump($req->getRequestVariableByKey("body")); //output - parsed JSON input
 *      }
 * }
 *
 * $router = Router::getInstance();
 *
 * $router->post("/comment/", new ExampleHandler(),[ new JsonBodyParserPlugin() ]);
 *
 * $router->handle();
*/

class JsonBodyParserPlugin implements IRouterPlugin {

    private int $maxDepth;
    private bool $useAssoc;
    private int $errorCode;
    private string $errorMessage;

    public function __construct(
        bool $useAssoc = true,
        int $maxDepth = 256,
        int $errorCode = 400,
        string $errorMessage = "Bad request"
    ) {
        $this->useAssoc = $useAssoc;
        $this->maxDepth = $maxDepth;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @throws RouteException
     * @param Request $req
     * @return Request
    */
    public function execute(Request $req): Request {
        try {
            $rawInput = file_get_contents("php://input");
            $jsonBody = json_decode($rawInput, $this->useAssoc, $this->maxDepth, JSON_THROW_ON_ERROR);
            $req->setRequestVariable("body", $jsonBody);
            return $req;
        } catch (Throwable $e) {
            throw new RouteException($this->errorMessage, $this->errorCode);
        }

    }
}