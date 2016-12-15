<?php
namespace SlimBootstrap\OutputWriter;

use \SlimBootstrap;
use \Slim;

/**
 * Class Factory
 *
 * @package SlimBootstrap\OutputWriter
 */
class Factory
{
    /**
     * @var array
     */
    private $csvConfig = [];

    /**
     * An array with the accepted Accept headers and the function name to create the response object for them.
     *
     * @var array
     */
    private $supportedMediaTypes = [
        'application/json' => 'createJson',
        'text/csv'         => 'createCsv',
    ];

    /**
     * Factory constructor.
     *
     * @param array $csvConfig
     */
    public function __construct(array $csvConfig = [])
    {
        $this->csvConfig = $csvConfig;
    }

    /**
     * @param Slim\Http\Response $response
     * @param string             $acceptHeader
     *
     * @return SlimBootstrap\OutputWriter
     *
     * @throws SlimBootstrap\Exception
     */
    public function create(
        Slim\Http\Response $response,
        string $acceptHeader
    ): SlimBootstrap\OutputWriter {
        if (null === $acceptHeader) {
            return $this->createJson($response);
        }

        $headers = \preg_split('/[,;]/', $acceptHeader);

        /**
         * Loop through accept headers and check if they are supported.
         * Use first supported accept header and create fitting OutputWriter
         */
        foreach ($headers as $header) {
            if (true === \array_key_exists($header, $this->supportedMediaTypes)) {
                $function = $this->supportedMediaTypes[$header];
                $instance = $this->$function($response);

                return $instance;
            }
        }

        if (true === \in_array('application/*', $headers)
            || \in_array('*/*', $headers)
        ) {
            return $this->createJson($response);
        }

        throw new SlimBootstrap\Exception(
            'media type not supported (supported media types: '
            . \implode(', ', \array_keys($this->supportedMediaTypes)) .  ')',
            406
        );
    }

    /**
     * This function creates a Json reponse object.
     *
     * @param Slim\Http\Response $response
     *
     * @return SlimBootstrap\OutputWriter\Json
     */
    private function createJson(Slim\Http\Response $response): SlimBootstrap\OutputWriter\Json
    {
        return new SlimBootstrap\OutputWriter\Json($response);
    }

    /**
     * This function creates a Csv reponse object.
     *
     * @param Slim\Http\Response $response
     *
     * @return SlimBootstrap\OutputWriter\Csv
     */
    private function createCsv(Slim\Http\Response $response): SlimBootstrap\OutputWriter\Csv
    {
        return new SlimBootstrap\OutputWriter\Csv($response, $this->csvConfig);
    }
}
