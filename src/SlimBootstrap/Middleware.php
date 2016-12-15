<?php
namespace SlimBootstrap;

use \Psr\Http\Message;

/**
 * Interface Middleware
 *
 * @package SlimBootstrap
 */
interface Middleware
{
    /**
     * @param Message\ServerRequestInterface $request
     * @param Message\ResponseInterface      $response
     * @param callable                       $next
     *
     * @return Message\ResponseInterface
     */
    public function execute(
        Message\ServerRequestInterface $request,
        Message\ResponseInterface $response,
        callable $next
    ): Message\ResponseInterface;
}
