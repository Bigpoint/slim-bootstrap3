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
     * This method is called to output the passed $data with the given $statusCode.
     *
     * @param Slim\Http\Response $response
     * @param array              $data       The actual data to output
     * @param int                $statusCode The HTTP status code to return
     *
     * @return Slim\Http\Response
     */
    public function write(Slim\Http\Response $response, array $data, int $statusCode = 200): Slim\Http\Response;
}
