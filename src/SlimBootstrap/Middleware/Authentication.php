<?php
namespace SlimBootstrap\Middleware;

use \Monolog;
use \Psr\Http\Message;
use \SlimBootstrap;
use \Slim;

class Authentication implements SlimBootstrap\Middleware
{
    /**
     * @var Monolog\Logger
     */
    private $logger = null;

    /**
     * @var SlimBootstrap\Authentication
     */
    private $authentication = null;
    /**
     * @var array
     */
    private $aclConfig = null;

    /**
     * Array that defines if the current endpoints wants authentication or not.
     * This array is only used if authentication in general is enabled. The
     * idea is to be able to disable authentication for one specific endpoint.
     *
     * @var array
     */
    private $endpointAuthentication = [];

    /**
     * @param Monolog\Logger               $logger
     * @param SlimBootstrap\Authentication $authentication
     * @param array                        $aclConfig
     */
    public function __construct(
        Monolog\Logger $logger,
        SlimBootstrap\Authentication $authentication = null,
        array $aclConfig = null
    ) {
        $this->logger         = $logger;
        $this->authentication = $authentication;
        $this->aclConfig      = $aclConfig;
    }

    /**
     * @param Message\ServerRequestInterface $request
     * @param Message\ResponseInterface      $response
     * @param callable                       $next
     *
     * @return Message\ResponseInterface
     */
    public function execute(
        Message\ServerRequestInterface $request,
        Message\ResponseInterface $response,
        callable $next
    ): Message\ResponseInterface {
        try {
            // use authentication for API
            if (null !== $this->authentication) {
                /** @var Slim\Route $currentRoute */
                $currentRoute = $request->getAttribute('route');
                $routeId      = $request->getMethod() . $currentRoute->getPattern();

                if (true === \array_key_exists($routeId, $this->endpointAuthentication)
                    && false === $this->endpointAuthentication[$routeId]
                ) {
                    return $next($request, $response);
                }

                if (false === \is_array($this->aclConfig)) {
                    throw new SlimBootstrap\Exception('acl config is empty or invalid', 500);
                }

                $this->logger->addInfo('using authentication');

                $clientId = $this->authentication->authenticate($request);

                $this->logger->addInfo('authentication successfull');

                $request = $request->withAttribute('clientId', $clientId);

                $this->logger->addNotice('set clientId to parameter: ' . $clientId);
                $this->logger->addDebug(\var_export($request->getQueryParams(), true));

                // TODO: acl
            }
        } catch (SlimBootstrap\Exception $exception) {
            // TODO
        }

        return $next($request, $response);
    }
}
