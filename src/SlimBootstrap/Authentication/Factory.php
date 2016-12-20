<?php
namespace SlimBootstrap\Authentication;

use \Http;
use \Monolog;
use \SlimBootstrap;

/**
 * Class Factory
 *
 * @package SlimBootstrap\Authentication
 */
class Factory
{
    /**
     * @var array
     */
    private $config = null;

    /**
     * @var Http\Caller
     */
    private $httpCaller = null;

    /**
     * @var Monolog\Logger
     */
    private $logger = null;

    /**
     * Factory constructor.
     *
     * @param array          $config
     * @param Http\Caller    $httpCaller
     * @param Monolog\Logger $logger
     */
    public function __construct(array $config, Http\Caller $httpCaller, Monolog\Logger $logger)
    {
        $this->config     = $config;
        $this->httpCaller = $httpCaller;
        $this->logger     = $logger;
    }

    /**
     * @return SlimBootstrap\Authentication\Oauth
     */
    public function createOauth(): SlimBootstrap\Authentication\Oauth
    {
        return new SlimBootstrap\Authentication\Oauth(
            $this->config['authenticationUrl'],
            $this->httpCaller,
            $this->logger
        );
    }

    public function createJwt(): SlimBootstrap\Authentication\Jwt
    {
        return new SlimBootstrap\Authentication\Jwt(
            $this->config['providerUrl'],
            $this->config['claims'],
            $this->httpCaller,
            $this->logger
        );
    }
}
