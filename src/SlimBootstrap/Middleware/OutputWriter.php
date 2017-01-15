<?php
namespace SlimBootstrap\Middleware;

use \Psr\Http\Message;
use \SlimBootstrap;
use \Slim;

/**
 * Class OutputWriter
 *
 * @package SlimBootstrap\Middleware
 */
class OutputWriter implements SlimBootstrap\Middleware
{
    /**
     * An array with the accepted Accept headers and the function name to create the response object for them.
     *
     * @var array
     */
    private $supportedMediaTypes = [
        'application/json' => 'createJson',
    ];

    /**
     * @var array
     */
    private $csvConfig = [];

    /**
     * @param array $csvConfig
     */
    public function __construct(array $csvConfig)
    {
        $this->csvConfig = $csvConfig;
    }

    /**
     * @param Message\ServerRequestInterface $request
     * @param Slim\Http\Response             $response
     * @param callable                       $next
     *
     * @return Message\ResponseInterface
     */
    public function execute(
        Message\ServerRequestInterface $request,
        Slim\Http\Response $response,
        callable $next
    ): Message\ResponseInterface {
        $request = $request->withAttribute(
            'outputWriter',
            $this->determineOutputWriter($request->getHeaderLine('Accept'))
        );

        return $next($request, $response);
    }

    /**
     * @param string $acceptHeader
     *
     * @return SlimBootstrap\OutputWriter
     *
     * @throws SlimBootstrap\Exception
     */
    private function determineOutputWriter(string $acceptHeader): SlimBootstrap\OutputWriter
    {
        if (null === $acceptHeader || '' === $acceptHeader) {
            return $this->createJson();
        }

        $headers = \preg_split('/[,;]/', $acceptHeader);

        /**
         * Loop through accept headers and check if they are supported.
         * Use first supported accept header and create fitting OutputWriter
         */
        foreach ($headers as $header) {
            if (true === \array_key_exists($header, $this->supportedMediaTypes)) {
                $function = $this->supportedMediaTypes[$header];
                $instance = $this->$function();

                return $instance;
            }
        }

        if (true === \in_array('application/*', $headers)
            || \in_array('*/*', $headers)
        ) {
            return $this->createJson();
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
     * @return SlimBootstrap\OutputWriter\Json
     */
    private function createJson(): SlimBootstrap\OutputWriter\Json
    {
        return new SlimBootstrap\OutputWriter\Json();
    }
}
