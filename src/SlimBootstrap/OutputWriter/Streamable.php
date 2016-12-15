<?php
namespace SlimBootstrap\OutputWriter;

use \Slim;

/**
 * Interface Streamable
 *
 * @package SlimBootstrap\OutputWriter
 */
interface Streamable
{
    /**
     * @param Slim\Http\Response $response The Slim response instance
     */
    public function __construct(Slim\Http\Response $response);

    /**
     * @param array $data
     */
    public function writeToStream(array $data);
}
