<?php
namespace SlimBootstrap\Middleware;

use \Monolog;
use \Psr\Http\Message;
use \SlimBootstrap;
use \Slim;

/**
 * Class Authentication
 *
 * @package SlimBootstrap\Middleware
 */
class Authentication implements SlimBootstrap\Middleware
{
    /**
     * @var Monolog\Logger
     */
    private $logger = null;

    /**
     * @var SlimBootstrap\Acl
     */
    private $acl = null;

    /**
     * @var SlimBootstrap\Authentication
     */
    private $authentication = null;

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
     * @param SlimBootstrap\Acl            $acl
     * @param SlimBootstrap\Authentication $authentication
     */
    public function __construct(
        Monolog\Logger $logger,
        SlimBootstrap\Acl $acl,
        SlimBootstrap\Authentication $authentication = null
    ) {
        $this->logger         = $logger;
        $this->acl            = $acl;
        $this->authentication = $authentication;
    }

    /**
     * @param Message\ServerRequestInterface $request
     * @param Slim\Http\Response             $response
     * @param callable                       $next
     *
     * @return Message\ResponseInterface
     *
     * @throws SlimBootstrap\Exception
     */
    public function execute(
        Message\ServerRequestInterface $request,
        Slim\Http\Response $response,
        callable $next
    ): Message\ResponseInterface {
        // use authentication for API
        if (null !== $this->authentication) {
            /** @var Slim\Route $currentRoute */
            $currentRoute = $request->getAttribute('route');

            if (null === $currentRoute) {
                return $next($request, $response);
            }

            $routeId = $request->getMethod() . $currentRoute->getPattern();

            if (true === \array_key_exists($routeId, $this->endpointAuthentication)
                && false === $this->endpointAuthentication[$routeId]
            ) {
                return $next($request, $response);
            }

            $aclConfig = $this->acl->getConfig();

            if (null !== $this->authentication &&
                (false === \is_array($aclConfig) || 0 === \count($aclConfig))
            ) {
                throw new SlimBootstrap\Exception('acl config is empty or invalid', 500);
            }

            $authenticationMethod = \get_class($this->authentication);

            $this->logger->addInfo(
                'authentication-usage',
                [
                    'method' => $authenticationMethod,
                ]
            );

            $clientData = $this->authentication->authenticate($request);
            $clientId   = $clientData['clientId'];
            $routeName  = $currentRoute->getName();

            $this->logger->addInfo(
                'authentication-successfull',
                [
                    'method'     => $authenticationMethod,
                    'clientId'   => $clientId,
                    'routeName'  => $routeName,
                    'clientData' => \var_export($clientData, true),
                ]
            );

            if (false === empty($clientData['role'])) {
                $this->acl->accessRole($clientData['role'], $routeName);
            } else {
                $this->acl->access($clientId, $routeName);
            }

            $this->logger->addInfo('access granted');

            $request = $request->withAttribute('clientId', $clientId);

            $this->logger->addNotice('set clientId to parameter: ' . $clientId);
            $this->logger->addDebug(\var_export($request->getQueryParams(), true));
        }

        return $next($request, $response);
    }

    /**
     * @param string $routeId
     * @param bool   $authentication
     */
    public function setEndpointAuthentication(string $routeId, bool $authentication)
    {
        $this->endpointAuthentication[$routeId] = $authentication;
    }
}
