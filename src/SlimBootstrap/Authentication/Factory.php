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
     *
     * @throws SlimBootstrap\Exception if "oauth" config is invalid
     */
    public function createOauth(): SlimBootstrap\Authentication\Oauth
    {
        if (false === \array_key_exists('oauth', $this->config)
            || false === \is_array($this->config['oauth'])
            || false === \array_key_exists('authenticationUrl', $this->config['oauth'])
            || false === \is_string($this->config['oauth']['authenticationUrl'])
            || true === empty($this->config['oauth']['authenticationUrl'])
        ) {
            throw new SlimBootstrap\Exception('"oauth" config invalid');
        }

        return new SlimBootstrap\Authentication\Oauth(
            $this->config['oauth']['authenticationUrl'],
            $this->httpCaller,
            $this->logger
        );
    }

    /**
     * @return SlimBootstrap\Authentication\Jwt
     *
     * @throws SlimBootstrap\Exception if "jwt" config is invalid
     */
    public function createJwt(): SlimBootstrap\Authentication\Jwt
    {
        if (false === \array_key_exists('jwt', $this->config)
            || false === \is_array($this->config['jwt'])
            || false === \array_key_exists('providerUrl', $this->config['jwt'])
            || false === \is_string($this->config['jwt']['providerUrl'])
            || true === empty($this->config['jwt']['providerUrl'])
            || false === \array_key_exists('claims', $this->config['jwt'])
            || false === \is_string($this->config['jwt']['claims'])
            || 0 === \count($this->config['jwt']['claims'])
        ) {
            throw new SlimBootstrap\Exception('"jwt" config invalid');
        }

        return new SlimBootstrap\Authentication\Jwt(
            $this->config['jwt']['providerUrl'],
            $this->config['jwt']['claims'],
            $this->httpCaller,
            $this->logger
        );
    }
}
