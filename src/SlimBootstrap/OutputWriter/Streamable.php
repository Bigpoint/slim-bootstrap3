<?php
namespace SlimBootstrap\OutputWriter;

use \Psr\Http\Message;

/**
 * Interface Streamable
 *
 * @package SlimBootstrap\OutputWriter
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
