<?php
namespace SlimBootstrap;

use \Slim;

/**
 * This interface represents the basic structure of all response classes.
 *
 * @package SlimBootstrap
 */
interface OutputWriter
{
    /**
     * @param Slim\Http\Response $response The Slim response instance
     */
    public function __construct(Slim\Http\Response $response);

    /**
     * This method is called to output the passed $data with the given $statusCode.
     *
     * @param array $data       The actual data to output
     * @param int   $statusCode The HTTP status code to return
     */
    public function write(array $data, int $statusCode = 200);
}
