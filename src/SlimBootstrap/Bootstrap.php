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
                \Exception $exception
            ) use ($container): Message\ResponseInterface {
                $code     = 500;
                $logLevel = Monolog\Logger::ERROR;
                if ($exception instanceof SlimBootstrap\Exception) {
                    $code     = $exception->getCode();
                    $logLevel = $exception->getLogLevel();
                }

                $container->logger->addRecord(
                    $logLevel,
                    \sprintf('%d - %s', $exception->getCode(), $exception->getMessage())
                );

                return $response->withStatus($code)
                    ->withHeader('Content-Type', 'text/html')
                    ->write($exception->getMessage());
            };
        };

        $this->registerMiddlewares($this->app, $logger);

        return $this->app;
    }

    /**
     * @param string                 $type           should be one of \SlimBootstrap\Bootstrap::HTTP_METHOD_*
     * @param string                 $route
     * @param string                 $name           name of the route to add (used in ACL)
     * @param SlimBootstrap\Endpoint $endpoint       should be one of \SlimBootstrap\Endpoint\*
     * @param bool                   $authentication set this to false if you want no authentication for this endpoint
     *                                               (default: true)
     */
    public function addEndpoint(
        string $type,
        string $route,
        string $name,
        SlimBootstrap\Endpoint $endpoint,
        bool $authentication = true
    ) {
        $this->validateEndpoint($type, $endpoint);

        $this->authenticationMiddleware->setEndpointAuthentication(\strtoupper($type) . $route, $authentication);

        $this->app->$type(
            $route,
            function (
                Message\ServerRequestInterface $request,
                Message\ResponseInterface $response,
                array $routeArguments
            ) use ($endpoint, $type): Slim\Http\Response {
                $clientId = $request->getAttribute('clientId');

                if (true === \is_string($clientId)) {
                    $endpoint->setClientId($clientId);
                }

                switch ($type) {
                    case self::HTTP_METHOD_DELETE:
                        // fall through to GET

                    case self::HTTP_METHOD_GET:
                        $data = $request->getQueryParams();
                        break;

                    case self::HTTP_METHOD_POST:
                        // fall through to PUT

                    case self::HTTP_METHOD_PUT:
                        $data = $request->getParsedBody();
                        break;

                    default:
                        $data = [];
                }


                $data = $endpoint->$type($routeArguments, $data);

                $outputWriter = $request->getAttribute('outputWriter');
                $newResponse  = $outputWriter->write($response, $data);

                return $newResponse;
            }
        )->setName($name);
    }

    /**
     * @param string                 $type
     * @param SlimBootstrap\Endpoint $endpoint
     *
     * @throws SlimBootstrap\Exception
     */
    private function validateEndpoint(string $type, SlimBootstrap\Endpoint $endpoint)
    {
        $interfaces = \class_implements($endpoint);
        $interface  = 'SlimBootstrap\\Endpoint\\' . \ucfirst($type);

        if (false === \array_key_exists($interface, $interfaces)) {
            throw new SlimBootstrap\Exception(
                'endpoint "' . \get_class($endpoint) . '" is not a valid ' . \strtoupper($type) . ' endpoint'
            );
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
        $this->outputWriterMiddleware   = $this->middlewareFactory->getOutputWriter();
        $this->authenticationMiddleware = $this->middlewareFactory->getAuthentication(
            $logger,
            $this->authentication,
            $this->aclConfig
        );

        $app->add([$this->outputWriterMiddleware, 'execute']);
        $app->add([$this->authenticationMiddleware, 'execute']);
        $app->add($this->middlewareFactory->getCache($this->applicationConfig['cacheDuration']));
        $app->add([$headerMiddleware, 'execute']);
        $app->add([$logMiddleware, 'execute']);
    }
}
