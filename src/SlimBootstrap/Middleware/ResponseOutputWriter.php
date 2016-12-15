<?php
namespace SlimBootstrap\Middleware;

use \Psr\Http\Message;
use \SlimBootstrap;

/**
 * Class ResponseOutputWriter
 *
 * @package SlimBootstrap\Middleware
 */
class ResponseOutputWriter implements SlimBootstrap\Middleware
{
    /**
     * @var SlimBootstrap\ResponseOutputwriter\Factory
     */
    private $responseOutputWriterFactory = null;

    /**
     * @var SlimBootstrap\ResponseOutputWriter
     */
    private $responseOutputWriter = null;

    /**
     * ResponseOutputWriter constructor.
     *
     * @param SlimBootstrap\ResponseOutputwriter\Factory $responseOutputWriterFactory
     */
    public function __construct(SlimBootstrap\ResponseOutputwriter\Factory $responseOutputWriterFactory)
    {
        $this->responseOutputWriterFactory = $responseOutputWriterFactory;
    }

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
        $this->responseOutputWriter = $this->responseOutputWriterFactory->create(
            $response,
            $request->getHeader('Accept')
        );

        return $next($request, $response);
    }

    /**
     * @return SlimBootstrap\ResponseOutputWriter
     */
    public function &getResponseOutputWriter(): SlimBootstrap\ResponseOutputWriter
    {
        return $this->responseOutputWriter;
    }
}
