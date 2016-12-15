<?php
namespace SlimBootstrap\Middleware;

use \Monolog;
use \Psr\Http\Message;
use \SlimBootstrap;
use \Slim;

/**
 * Class Log
 *
 * @package SlimBootstrap\Middleware
 */
class Log implements SlimBootstrap\Middleware
{
    /**
     * @var Monolog\Logger
     */
    private $logger = null;

    /**
     * @param Monolog\Logger $logger
     */
    public function __construct(Monolog\Logger $logger)
    {
        $this->logger = $logger;
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
        $this->logger->debug(\sprintf('Request: %s - %s', $request->getMethod(), $request->getUri()->getPath()));

        /** @var Message\ResponseInterface $response */
        $response = $next($request, $response);

        $this->logger->debug(\sprintf('Response status: %s', $response->getStatusCode()));

        return $response;
    }
}
