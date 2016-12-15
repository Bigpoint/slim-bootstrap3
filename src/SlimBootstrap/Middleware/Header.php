<?php
namespace SlimBootstrap\Middleware;

use \Psr\Http\Message;
use \SlimBootstrap;

/**
 * Class Header
 *
 * @package SlimBootstrap\Middleware
 */
class Header implements SlimBootstrap\Middleware
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
    ): Message\ResponseInterface {
        $response = $response->withAddedHeader(
            'Access-Control-Allow-Origin',
            '*'
        );

        return $next($request, $response);
    }
}
