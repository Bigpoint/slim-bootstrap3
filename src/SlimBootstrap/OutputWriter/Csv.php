<?php
namespace SlimBootstrap\OutputWriter;

use \SlimBootstrap;
use \Slim;

/**
* This class is responsible to output the data to the client in valid CSV format.
*
* @package SlimBootstrap\OutputWriter
*/
class Csv implements SlimBootstrap\OutputWriter
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param Slim\Http\Response $response
     * @param array              $data
     * @param int                $statusCode
     *
     * @return Slim\Http\Response
     */
    public function write(Slim\Http\Response $response, array $data, int $statusCode = 200): Slim\Http\Response
    {
        // TODO: Implement write() method.

        return $response;
    }
}
