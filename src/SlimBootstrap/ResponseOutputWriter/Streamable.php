<?php
namespace SlimBootstrap\ResponseOutputWriter;

use \Psr\Http\Message;

/**
 * Interface Streamable
 *
 * @package SlimBootstrap\ResponseOutputWriter
 */
interface Streamable
{
    /**
     * @param Message\ResponseInterface $response The Slim response instance
     */
    public function __construct(Message\ResponseInterface $response);

    /**
     * @param array $data
     */
    public function writeToStream(array $data);
}
