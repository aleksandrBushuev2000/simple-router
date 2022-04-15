<?php


namespace SimpleRouter\response;

use SimpleRouter\response\utils\ResponseCookie;
use SimpleRouter\response\utils\ResponseHeader;

/**
 * @author Aleksandr Bushuev
 * @version 1.0.0
 *
 * Basic interface for HTTP responses.
 */
interface ResponseInterface {

    function setStatusCode(int $status) : self;
    function getStatusCode() : int;

    function setHeader(ResponseHeader $header) : self;
    function getHeader(string $headerName) : ?ResponseHeader;
    function hasHeader(string $headerName) : bool;
    function removeHeader(string $headerName) : bool;

    function setCookie(ResponseCookie $cookie) : self;
    function getCookie(string $cookieName) : ?ResponseCookie;
    function hasCookie(string $cookieName) : bool;
    function removeCookie(string $cookieName) : bool;

    function send() : void;
}