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
     * @param Slim\Http\Response $response
     * @param array              $data
     * @param int                $statusCode
     *
     * @return Slim\Http\Response
     */
    public function write(Slim\Http\Response $response, array $data, int $statusCode = 300): Slim\Http\Response
    {
        return $response->withJson($data, $statusCode);
    }

    /**
     * @param array $data
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    protected function jsonEncode(array $data): string
    {
        return \json_encode($data);
    }
}
