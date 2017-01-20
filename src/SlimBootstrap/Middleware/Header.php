<?php
namespace SlimBootstrap\Middleware;

use \Psr\Http\Message;
use \SlimBootstrap;
use \Slim;

/**
 * Class Header
 *
 * @package SlimBootstrap\Middleware
 */
class Header implements SlimBootstrap\Middleware
{
    /**
     * @var array
     */
    private $headers = [];

    /**
     * Header constructor.
     *
     * @param array $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

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
    ): Message\ResponseInterface {
        foreach ($this->headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }

        return $next($request, $response);
    }
}
