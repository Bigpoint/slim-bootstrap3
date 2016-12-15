<?php
namespace SlimBootstrap\OutputWriter;

use \SlimBootstrap;
use \Slim;

/**
* This class is responsible to output the data to the client in valid JSON format.
*
* @package SlimBootstrap\OutputWriter
*/
class Json implements SlimBootstrap\OutputWriter
{
    /**
     * @var Slim\Http\Response
     */
    private $response = null;

    /**
     * Json constructor.
     *
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
        $this->response = $this->response->withJson($data, $statusCode);
    }
}
