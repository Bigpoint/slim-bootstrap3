<?php
namespace SlimBootstrap;

use \Monolog;
use \SlimBootstrap;
use \Slim;

/**
 * Class Bootstrap
 *
 * @package SlimBootstrap
 */
class Bootstrap
{
    /**
     * @var array
     */
    private $applicationConfig = null;

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
    private $aclConfig = null;

    /**
     * @var Slim\App
     */
    private $app = null;

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
        array $aclConfig = null
    ) {
        $this->applicationConfig = $applicationConfig;
        $this->middlewareFactory = $middlewareFactory;
        $this->authentication    = $authentication;
        $this->aclConfig         = $aclConfig;
    }

    /**
     * @param Monolog\Logger $logger
     */
    public function init(Monolog\Logger $logger)
    {
        $this->app = new Slim\App(
            [
                'settings' => [
                    'determineRouteBeforeAppMiddleware' => true,
                    'displayErrorDetails'               => $this->applicationConfig['displayErrorDetails'],
                ],
            ]
        );

        // add a logger
        $container = $this->app->getContainer();
        $container['logger'] = function($container) use ($logger) {
            return $logger;
        };

        $this->registerMiddlewares($this->app, $logger);
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
        $responseOutputWriterMiddleware = $this->middlewareFactory->getResponseOutputWriter(
            $this->createResponseOutputWriterFactory()
        );
        $authenticationMiddleware       = $this->middlewareFactory->getAuthentication(
            $logger,
            $this->authentication,
            $this->aclConfig
        );

        $app->add([$authenticationMiddleware, 'execute']);
        $app->add([$responseOutputWriterMiddleware, 'execute']);
        $app->add(
            new Slim\HttpCache\Cache(
                'public',
                $this->applicationConfig['cacheDuration']
            )
        );
        $app->add([$headerMiddleware, 'execute']);
        $app->add([$logMiddleware, 'execute']);
    }

    /**
     * @return SlimBootstrap\ResponseOutputwriter\Factory
     */
    private function createResponseOutputWriterFactory(): SlimBootstrap\ResponseOutputwriter\Factory
    {
        $csvConfig = [];

        if (true === \array_key_exists('csv', $this->applicationConfig)
            && true === \is_array($this->applicationConfig['csv'])
        ) {
            $csvConfig = $this->applicationConfig['csv'];
        }

        return new SlimBootstrap\ResponseOutputwriter\Factory($csvConfig);
    }
}
