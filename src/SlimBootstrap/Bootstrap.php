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
     * @var SlimBootstrap\Authentication|string
     */
    private $authentication = null;

    /**
     * @var \Monolog\Logger
     */
    private $logger = null;

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
     * @param array $applicationConfig
     */
    public function __construct(array $applicationConfig = [])
    {
        $this->applicationConfig = $applicationConfig;
        $this->logger            = new Monolog\Logger('slim-bootstrap3');
    }

    /**
     * @param \Monolog\Logger $logger
     *
     * @return SlimBootstrap\Bootstrap
     */
    public function setLogger(Monolog\Logger $logger): SlimBootstrap\Bootstrap
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param string|SlimBootstrap\Authentication $authentication
     *
     * @return SlimBootstrap\Bootstrap
     *
     * @throws SlimBootstrap\Exception if the authentication module is invalid
     */
    public function setAuthentication($authentication): SlimBootstrap\Bootstrap
    {
        if (true === \is_string($authentication)) { // resolve internal authentication method
            $authenticationFactory = new SlimBootstrap\Authentication\Factory($this->logger);

            $method = 'create' . \ucfirst($authentication);

            if (false === \method_exists($authenticationFactory, $method)
                || false === \is_callable([$authenticationFactory, $method])
            ) {
                throw new SlimBootstrap\Exception(\sprintf('auth method "%s" does not exist', $authentication));
            }

            $this->authentication = $authenticationFactory->$method($this->applicationConfig);
        } else { // use provided authentication implementation
            if (!($authentication instanceof SlimBootstrap\Authentication)) {
                throw new SlimBootstrap\Exception(
                    'The authentication module has to implement the \SlimBootstrap\Authentication interface'
                );
            }

            $this->authentication = $authentication;
        }

        return $this;
    }

    /**
     * @param array $endpoints
     *
     * @throws Exception
     * @throws Slim\Exception\MethodNotAllowedException
     * @throws Slim\Exception\NotFoundException
     */
    public function run(array $endpoints)
    {
        $displayErrorDetails = false;

        if (true === \array_key_exists('displayErrorDetails', $this->applicationConfig)
            && true === \is_bool($this->applicationConfig['displayErrorDetails'])
        ) {
            $displayErrorDetails = $this->applicationConfig['displayErrorDetails'];
        }

        $this->app = new Slim\App(
            [
                'settings' => [
                    'determineRouteBeforeAppMiddleware' => true,
                    'displayErrorDetails'               => $displayErrorDetails,
                ],
            ]
        );
        $container = $this->app->getContainer();

        // add a logger
        $container['logger'] = function ($container) {
            return $this->logger;
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
                    ->withAddedHeader('Content-Type', 'text/html')
                    ->write($exception->getMessage());
            };
        };

        $this->registerMiddlewares($this->app);

        if (0 === \count($endpoints)) {
            throw new SlimBootstrap\Exception('At least one endpoint has to be defined');
        }

        $this->registerEndpoints($endpoints);

        $this->app->run();
    }

    /**
     * @param array $endpoints
     *
     * @throws Exception
     */
    private function registerEndpoints(array $endpoints)
    {
        $endpoints[] = [
            'type'     => self::HTTP_METHOD_GET,
            'route'    => 'info',
            'name'     => 'info',
            'instance' => new SlimBootstrap\Endpoint\Info(),
        ];

        for ($i = 0; $i < \count($endpoints); $i += 1) {
            $endpoint = $endpoints[$i];

            $this->validateEndpoint($i, $endpoint);

            $type           = $endpoint['type'];
            $route          = $endpoint['route'];
            $name           = $endpoint['name'];
            $instance       = $endpoint['instance'];
            $authentication = true;

            if (true === \array_key_exists('authentication', $endpoint)) {
                $authentication = $endpoint['authentication'];
            }

            $this->addEndpoint($type, $route, $name, $instance, $authentication);
        }
    }

    /**
     * @param int   $i
     * @param array $endpoint
     *
     * @throws SlimBootstrap\Exception
     */
    private function validateEndpoint(int $i, array $endpoint)
    {
        // validate structure
        if (false === \array_key_exists('type', $endpoint)
            || true === empty($endpoint['type'])
            || false === \array_key_exists('route', $endpoint)
            || true === empty($endpoint['route'])
            || false === \array_key_exists('name', $endpoint)
            || true === empty($endpoint['name'])
            || false === \array_key_exists('instance', $endpoint)
            || false === \is_object($endpoint['instance'])
            || (true === \array_key_exists('authenticate', $endpoint)
                && false === \is_bool($endpoint['authenticate']))
        ) {
            throw new SlimBootstrap\Exception(
                \sprintf(
                    'endpoint definition #%s has invalid structure',
                    $i
                )
            );
        }

        // vlaidate implementation
        $interfaces = \class_implements($endpoint['instance']);
        $interface  = 'SlimBootstrap\\Endpoint\\' . \ucfirst($endpoint['type']);

        if (false === \array_key_exists($interface, $interfaces)) {
            throw new SlimBootstrap\Exception(
                \sprintf(
                    'endpoint "%s" of endpoint definition #%s is not a valid %s endpoint',
                    \get_class($endpoint),
                    $i,
                    \strtoupper($endpoint['type'])
                )
            );
        }
    }

    /**
     * @param string                 $type           should be one of \SlimBootstrap\Bootstrap::HTTP_METHOD_*
     * @param string                 $route
     * @param string                 $name           name of the route to add (used in ACL)
     * @param SlimBootstrap\Endpoint $endpoint       should be one of \SlimBootstrap\Endpoint\*
     * @param bool                   $authentication set this to false if you want no authentication for this endpoint
     *                                               (default: true)
     */
    private function addEndpoint(
        string $type,
        string $route,
        string $name,
        SlimBootstrap\Endpoint $endpoint,
        bool $authentication = true
    ) {
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


                $data = $endpoint->$type($routeArguments, $request->getQueryParams(), $data);

                $outputWriter = $request->getAttribute('outputWriter');
                $newResponse  = $outputWriter->write($response, $data);

                return $newResponse;
            }
        )->setName($name);
    }

    /**
     * Register middlewares (last in first executed).
     *
     * @param \Slim\App $app
     *
     * @throws SlimBootstrap\Exception
     */
    private function registerMiddlewares(Slim\App $app)
    {
        $middlewareFactory = new SlimBootstrap\Middleware\Factory();

        $this->outputWriterMiddleware   = $middlewareFactory->getOutputWriter();
        $this->authenticationMiddleware = $middlewareFactory->getAuthentication(
            $this->logger,
            $this->authentication,
            $this->applicationConfig
        );

        $app->add([$this->outputWriterMiddleware, 'execute']);
        $app->add([$this->authenticationMiddleware, 'execute']);
        $app->add($middlewareFactory->getCache($this->applicationConfig));
        $app->add([$middlewareFactory->getHeader($this->applicationConfig), 'execute']);
        $app->add([$middlewareFactory->getLog($this->logger), 'execute']);
    }
}
