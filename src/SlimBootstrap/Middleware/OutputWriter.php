<?php
namespace SlimBootstrap\Middleware;

use \Psr\Http\Message;
use \SlimBootstrap;

/**
 * Class OutputWriter
 *
 * @package SlimBootstrap\Middleware
 */
class OutputWriter implements SlimBootstrap\Middleware
{
    /**
     * @var SlimBootstrap\Outputwriter\Factory
     */
    private $outputWriterFactory = null;

    /**
     * @var SlimBootstrap\OutputWriter
     */
    private $outputWriter = null;

    /**
     * @param SlimBootstrap\Outputwriter\Factory $outputWriterFactory
     */
    public function __construct(SlimBootstrap\Outputwriter\Factory $outputWriterFactory)
    {
        $this->outputWriterFactory = $outputWriterFactory;
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
        $this->outputWriter = $this->outputWriterFactory->create($response, $request->getHeader('Accept'));

        return $next($request, $response);
    }

    /**
     * @return SlimBootstrap\OutputWriter
     */
    public function &getOutputWriter(): SlimBootstrap\OutputWriter
    {
        return $this->outputWriter;
    }
}
