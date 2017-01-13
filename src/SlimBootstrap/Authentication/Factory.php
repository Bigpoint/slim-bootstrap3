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
     * @param Http\Caller    $httpCaller
     * @param Monolog\Logger $logger
     */
    public function __construct(Http\Caller $httpCaller, Monolog\Logger $logger)
    {
        $this->httpCaller = $httpCaller;
        $this->logger     = $logger;
    }

    /**
     * @param array $config
     *
     * @return SlimBootstrap\Authentication\Oauth
     *
     * @throws SlimBootstrap\Exception if "oauth" config is invalid
     */
    public function createOauth(array $config): SlimBootstrap\Authentication\Oauth
    {
        if (false === \array_key_exists('oauth', $config)
            || false === \is_array($config['oauth'])
            || false === \array_key_exists('authenticationUrl', $config['oauth'])
            || false === \is_string($config['oauth']['authenticationUrl'])
            || true === empty($config['oauth']['authenticationUrl'])
        ) {
            throw new SlimBootstrap\Exception('"oauth" config invalid');
        }

        return new SlimBootstrap\Authentication\Oauth(
            $config['oauth']['authenticationUrl'],
            $this->httpCaller,
            $this->logger
        );
    }

    /**
     * @param array $config
     *
     * @return SlimBootstrap\Authentication\Jwt
     *
     * @throws SlimBootstrap\Exception if "jwt" config is invalid
     */
    public function createJwt(): SlimBootstrap\Authentication\Jwt
    {
        if (false === \array_key_exists('jwt', $config)
            || false === \is_array($config['jwt'])
            || false === \array_key_exists('providerUrl', $config['jwt'])
            || false === \is_string($config['jwt']['providerUrl'])
            || true === empty($config['jwt']['providerUrl'])
            || false === \array_key_exists('claims', $config['jwt'])
            || false === \is_string($config['jwt']['claims'])
            || 0 === \count($config['jwt']['claims'])
        ) {
            throw new SlimBootstrap\Exception('"jwt" config invalid');
        }

        return new SlimBootstrap\Authentication\Jwt(
            $config['jwt']['providerUrl'],
            $config['jwt']['claims'],
            $this->httpCaller,
            $this->logger
        );
    }
}
