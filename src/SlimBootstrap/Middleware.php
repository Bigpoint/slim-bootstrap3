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
    public function execute(
        Message\ServerRequestInterface $request,
        Message\ResponseInterface $response,
        callable $next
    ): Message\ResponseInterface;
}
