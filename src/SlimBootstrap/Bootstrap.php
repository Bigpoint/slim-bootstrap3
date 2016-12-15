<?php
namespace SlimBootstrap;

use \Monolog;
use \Psr\Http\Message;
use \SlimBootstrap;
use \Slim;

/**
 * Class Bootstrap
 *
 * @package SlimBootstrap
 *
 * @todo:
 *  - SlimBootstrap\Endpoint\ForceDefaultMimeType
 *  - mimetype handling
 *  - outputWriter as middleware?
 *  - Response is \Psr\Http\Message\StreamInterface
 */
class Bootstrap
{
    const HTTP_METHOD_DELETE = 'delete';
    const HTTP_METHOD_GET    = 'get';
    const HTTP_METHOD_POST   = 'post';
    const HTTP_METHOD_PUT    = 'put';

    /**
     * @var array
     */
    private $applicationConfig = [];

    /**
     * @var SlimBootstrap\Middleware\Factory
     */
    private $middlewareFactory = null;

    /**
     * @var SlimBootstrap\Authentication
     */
    private $authentication = null;

    /**
     * @var array
     */
    private $aclConfig = [];

    /**
     * @var Slim\App
     */
    private $app = null;

    /**
     * @var SlimBootstrap\Middleware\OutputWriter
     */
    private $outputWriterMiddleware = null;

    /**
     * @var SlimBootstrap\Middleware\Authentication
     */
    private $authenticationMiddleware = null;

    /**
     * @param array                            $applicationConfig
     * @param SlimBootstrap\Middleware\Factory $middlewareFactory
     * @param SlimBootstrap\Authentication     $authentication
     * @param array                            $aclConfig
     */
    public function __construct(
        array $applicationConfig,
        SlimBootstrap\Middleware\Factory $middlewareFactory,
        SlimBootstrap\Authentication $authentication = null,
        array $aclConfig = []
    ) {
        $this->applicationConfig = $applicationConfig;
        $this->middlewareFactory = $middlewareFactory;
        $this->authentication    = $authentication;
        $this->aclConfig         = $aclConfig;
    }

    /**
     * @param Monolog\Logger $logger
     *
     * @return Slim\App
     */
    public function init(Monolog\Logger $logger): Slim\App
    {
        $this->app = new Slim\App(
            [
                'settings' => [
                    'determineRouteBeforeAppMiddleware' => true,
                    'displayErrorDetails'               => $this->applicationConfig['displayErrorDetails'],
                ],
            ]
        );
        $container = $this->app->getContainer();

        // add a logger
        $container['logger'] = function ($container) use ($logger) {
            return $logger;
        };

        // add an errorHandler
        $container['errorHandler'] = function ($container) {
            return function (
                Message\ServerRequestInterface $request,
                Slim\Http\Response $response,
                SlimBootstrap\Exception $exception
            ) use ($container): Message\ResponseInterface {
                $response = $response->withStatus($exception->getLogLevel());
                $response->getBody()->write($exception->getMessage());

                return $response;
            };
        };

        $this->registerMiddlewares($this->app, $logger);

        return $this->app;
    }

    /**
     * @param string                 $type           should be one of \SlimBootstrap\Bootstrap::HTTP_METHOD_*
     * @param string                 $route
     * @param string                 $name           name of the route to add (used in ACL)
     * @param SlimBootstrap\Endpoint $endpoint       should be one of \SlimBootstrap\Endpoint\Collection*
     * @param bool                   $authentication set this to false if you want no authentication for this endpoint
     *                                               (default: true)
     */
    public function addCollectionEndpoint(
        string $type,
        string $route,
        string $name,
        SlimBootstrap\Endpoint $endpoint,
        bool $authentication = true
    ) {
        $this->validateEndpoint($type, $endpoint, 'Collection');

        $this->authenticationMiddleware->setEndpointAuthentication(\strtoupper($type) . $route, $authentication);

        $this->app->$type(
            $route,
            function (
                Message\ServerRequestInterface $request,
                Message\ResponseInterface $response,
                array $args
            ) use ($endpoint, $type) {
                $this->handleEndpointCall($endpoint, $type, $request, $args);
            }
        )->setName($name);
    }

    /**
     * @param string                 $type           should be one of \SlimBootstrap\Bootstrap::HTTP_METHOD_*
     * @param string                 $route
     * @param string                 $name           name of the route to add (used in ACL)
     * @param SlimBootstrap\Endpoint $endpoint       should be one of \SlimBootstrap\Endpoint\Resource*
     * @param bool                   $authentication set this to false if you want no authentication for this endpoint
     *                                               (default: true)
     */
    public function addResourceEndpoint(
        string $type,
        string $route,
        string $name,
        SlimBootstrap\Endpoint $endpoint,
        bool $authentication = true
    ) {
        $this->validateEndpoint($type, $endpoint, 'Resource');

        $this->authenticationMiddleware->setEndpointAuthentication(\strtoupper($type) . $route, $authentication);

        $this->app->$type(
            $route,
            function (
                Message\ServerRequestInterface $request,
                Slim\Http\Response $response,
                array $args
            ) use ($endpoint, $type) {
                $this->handleEndpointCall($endpoint, $type, $request, $args);
            }
        )->setName($name);
    }

    /**
     * @param string                 $type
     * @param SlimBootstrap\Endpoint $endpoint
     * @param string                 $endpointType
     *
     * @throws SlimBootstrap\Exception
     */
    private function validateEndpoint(string $type, SlimBootstrap\Endpoint $endpoint, string $endpointType)
    {
        $interfaces = \class_implements($endpoint);
        $interface  = 'SlimBootstrap\\Endpoint\\' . $endpointType . \ucfirst($type);

        if (false === \array_key_exists($interface, $interfaces)) {
            throw new SlimBootstrap\Exception(
                'endpoint "' . \get_class($endpoint) . '" is not a valid '
                . $endpointType . ' ' . \strtoupper($type) . ' endpoint'
            );
        }
    }

    /**
     * @param SlimBootstrap\Endpoint         $endpoint
     * @param string                         $type
     * @param Message\ServerRequestInterface $request
     * @param array                          $args
     *
     * @throws SlimBootstrap\Exception
     */
    private function handleEndpointCall(
        SlimBootstrap\Endpoint $endpoint,
        string $type,
        Message\ServerRequestInterface $request,
        array $args
    ) {
        $endpoint->setClientId($request->getAttribute('clientId'));

        $outputWriter = &$this->outputWriterMiddleware->getOutputWriter();

        if ($endpoint instanceof SlimBootstrap\Endpoint\Streamable) {
            if ($outputWriter instanceof SlimBootstrap\OutputWriter\Streamable) {
                $endpoint->setOutputWriter($outputWriter);

                \ob_start();
                $endpoint->$type($args);
                \ob_end_clean();
            } else {
                throw new SlimBootstrap\Exception(
                    'media type does not support streaming',
                    406,
                    Monolog\Logger::WARNING
                );
            }
        } else {
            $data = $endpoint->$type($args);

            $outputWriter->write($data);
        }
    }

    /**
     * Register middlewares (last in first executed).
     *
     * @param \Slim\App       $app
     * @param \Monolog\Logger $logger
     */
    private function registerMiddlewares(Slim\App $app, Monolog\Logger $logger)
    {
        $logMiddleware                  = $this->middlewareFactory->getLog($logger);
        $headerMiddleware               = $this->middlewareFactory->getHeader();
        $this->outputWriterMiddleware   = $this->middlewareFactory->getOutputWriter($this->createOutputWriterFactory());
        $this->authenticationMiddleware = $this->middlewareFactory->getAuthentication(
            $logger,
            $this->authentication,
            $this->aclConfig
        );

        $app->add([$this->authenticationMiddleware, 'execute']);
        $app->add([$this->outputWriterMiddleware, 'execute']);
        $app->add(new Slim\HttpCache\Cache('public', $this->applicationConfig['cacheDuration']));
        $app->add([$headerMiddleware, 'execute']);
        $app->add([$logMiddleware, 'execute']);
    }

    /**
     * @return SlimBootstrap\Outputwriter\Factory
     */
    private function createOutputWriterFactory(): SlimBootstrap\Outputwriter\Factory
    {
        $csvConfig = [];

        if (true === \array_key_exists('csv', $this->applicationConfig)
            && true === \is_array($this->applicationConfig['csv'])
        ) {
            $csvConfig = $this->applicationConfig['csv'];
        }

        return new SlimBootstrap\Outputwriter\Factory($csvConfig);
    }
}
