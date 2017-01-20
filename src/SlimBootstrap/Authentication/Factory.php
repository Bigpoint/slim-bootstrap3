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
     * @var Monolog\Logger
     */
    private $logger = null;

    /**
     * Factory constructor.
     *
     * @param Monolog\Logger $logger
     */
    public function __construct(Monolog\Logger $logger)
    {
        $this->logger = $logger;
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

        $oauthConfig   = $config['oauth'];
        $clientIdField = 'entity_id';

        if (true === \array_key_exists('clientIdField', $oauthConfig)
            && true === is_string($oauthConfig['clientIdField'])
            && false === empty($oauthConfig['clientIdField'])
        ) {
            $clientIdField = $oauthConfig['clientIdField'];
        }

        return new SlimBootstrap\Authentication\Oauth(
            $oauthConfig['authenticationUrl'],
            $clientIdField,
            new Http\Caller($this->logger),
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
    public function createJwt(array $config): SlimBootstrap\Authentication\Jwt
    {
        if (false === \array_key_exists('jwt', $config)
            || false === \is_array($config['jwt'])
            || false === \array_key_exists('publicKey', $config['jwt'])
            || false === \is_string($config['jwt']['publicKey'])
            || true === empty($config['jwt']['publicKey'])
            || false === \array_key_exists('claims', $config['jwt'])
            || false === \is_array($config['jwt']['claims'])
            || 0 === \count($config['jwt']['claims'])
        ) {
            throw new SlimBootstrap\Exception('"jwt" config invalid');
        }

        $jwtConfig        = $config['jwt'];
        $clientDataClaims = [
            'clientId' => 'name',
            'role'     => 'role',
        ];

        if (true === \array_key_exists('clientDataClaims', $jwtConfig)
            && true === \is_array($jwtConfig['clientDataClaims'])
            && true === \array_key_exists('clientId', $jwtConfig['clientDataClaims'])
            && false === empty($jwtConfig['clientDataClaims']['clientId'])
            && true === \array_key_exists('role', $jwtConfig['clientDataClaims'])
            && false === empty($jwtConfig['clientDataClaims']['role'])
        ) {
            $clientDataClaims = $jwtConfig['clientDataClaims'];
        }

        $encryption = '';

        if (true === \array_key_exists('encryption', $jwtConfig)
            && true === \is_string($jwtConfig['encryption'])
            && false === empty($jwtConfig['encryption'])
        ) {
            $encryption = $jwtConfig['encryption'];
        }

        return new SlimBootstrap\Authentication\Jwt(
            $jwtConfig['publicKey'],
            $encryption,
            $clientDataClaims,
            $jwtConfig['claims'],
            new Http\Caller($this->logger),
            $this->logger
        );
    }
}
