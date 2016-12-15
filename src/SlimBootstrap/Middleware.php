<?php
namespace SlimBootstrap;

use \Psr\Http\Message;
use \Slim;

/**
 * Interface Middleware
 *
 * @package SlimBootstrap
 */
interface Middleware
{
    /**
     * @param Message\ServerRequestInterface $request
     * @param Slim\Http\Response             $response
     * @param callable                       $next
     *
     * @return Message\ResponseInterface
     */
    public function execute(
        Message\ServerRequestInterface $request,
        Slim\Http\Response $response,
        callable $next
    ): Message\ResponseInterface;
}
