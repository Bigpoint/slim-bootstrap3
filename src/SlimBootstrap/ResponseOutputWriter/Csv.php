<?php
namespace SlimBootstrap\ResponseOutputWriter;

use Psr\Http\Message;
use \SlimBootstrap;

/**
* This class is responsible to output the data to the client in valid CSV format.
*
* @package SlimBootstrap\ResponseOutputWriter
*/
class Csv implements SlimBootstrap\ResponseOutputWriter
{
    /**
     * @var Message\ResponseInterface
     */
    private $response = null;

    /**
     * @param Message\ResponseInterface $response
     */
    public function __construct(Message\ResponseInterface $response)
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
