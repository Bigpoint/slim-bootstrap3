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
     * @var Slim\Http\Response
     */
    private $response = null;

    /**
     * @param Slim\Http\Response $response
     */
    public function __construct(Slim\Http\Response $response)
    {
        $this->response = $response;
    }

    /**
     * @param array $data
     * @param int   $statusCode
     */
    public function write(array $data, int $statusCode = 200)
    {
        // TODO: Implement write() method.
    }
}
