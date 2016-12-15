<?php
namespace SlimBootstrap\ResponseOutputWriter;

use \Psr\Http\Message;
use \SlimBootstrap;

/**
 * Class Factory
 *
 * @package SlimBootstrap\ResponseOutputWriter
 */
class Factory
{
    /**
     * @var array
     */
    private $csvConfig = [];

    /**
     * An array with the accepted Accept headers and the function name to
     * create the response object for them.
     *
     * @var array
     */
    private $supportedMediaTypes = array(
        'application/json' => 'createJson',
        'text/csv'         => 'createCsv',
    );

    /**
     * Factory constructor.
     *
     * @param array $csvConfig
     */
    public function __construct(
        array $csvConfig = []
    ) {
        $this->csvConfig = $csvConfig;
    }

    /**
     * @param Message\ResponseInterface $response
     * @param string                    $acceptHeader
     *
     * @return SlimBootstrap\ResponseOutputWriter
     *
     * @throws SlimBootstrap\Exception
     */
    public function create(
        Message\ResponseInterface $response,
        string $acceptHeader
    ): SlimBootstrap\ResponseOutputWriter {
        if (null === $acceptHeader) {
            return $this->createJson($response);
        }

        $headers = \preg_split('/[,;]/', $acceptHeader);

        /**
         * Loop through accept headers and check if they are supported.
         * Use first supported accept header and create fitting
         * ResponseOutputWriter
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
     * @param Message\ResponseInterface $response
     *
     * @return SlimBootstrap\ResponseOutputWriter\Json
     */
    private function createJson(Message\ResponseInterface $response): SlimBootstrap\ResponseOutputWriter\Json
    {
        return new SlimBootstrap\ResponseOutputWriter\Json(
            $response
        );
    }

    /**
     * This function creates a Csv reponse object.
     *
     * @param Message\ResponseInterface $response
     *
     * @return SlimBootstrap\ResponseOutputWriter\Csv
     */
    private function createCsv(Message\ResponseInterface $response): SlimBootstrap\ResponseOutputWriter\Csv
    {
        return new SlimBootstrap\ResponseOutputWriter\Csv(
            $response,
            $this->csvConfig
        );
    }
}
