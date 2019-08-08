<?php
namespace SlimBootstrap\Middleware;

use \Monolog;
use \SlimBootstrap;
use \Slim;

/**
 * Class Factory
 *
 * @package SlimBootstrap\Middleware
 */
class Factory
{
    /**
     * @param Monolog\Logger $logger
     *
     * @return SlimBootstrap\Middleware\Log
     */
    public function getLog(Monolog\Logger $logger): SlimBootstrap\Middleware\Log
    {
        return new SlimBootstrap\Middleware\Log($logger);
    }

    /**
     * @param array $applicationConfig
     *
     * @return SlimBootstrap\Middleware\Header
     */
    public function getHeader(array $applicationConfig): SlimBootstrap\Middleware\Header
    {
        $headers = [];

        if (true === \array_key_exists('headers', $applicationConfig)
            && true === \is_array($applicationConfig['headers'])
        ) {
            $headers = $applicationConfig['headers'];
        }

        return new SlimBootstrap\Middleware\Header($headers);
    }

    /**
     * @return SlimBootstrap\Middleware\OutputWriter
     */
    public function getOutputWriter(): SlimBootstrap\Middleware\OutputWriter
    {
        return new SlimBootstrap\Middleware\OutputWriter();
    }

    /**
     * @param Monolog\Logger                         $logger
     * @param \SlimBootstrap\AuthenticationInterface $authentication
     * @param array                                  $applicationConfig
     *
     * @return \SlimBootstrap\Middleware\Authentication
     *
     * @throws SlimBootstrap\Exception
     */
    public function getAuthentication(
        Monolog\Logger $logger,
        SlimBootstrap\AuthenticationInterface $authentication = null,
        array $applicationConfig
    ): SlimBootstrap\Middleware\Authentication {
        $aclConfig = [];

        if (true === \array_key_exists('acl', $applicationConfig)
            && true === \is_array($applicationConfig['acl'])
        ) {
            $aclConfig = $applicationConfig['acl'];
        }

        return new SlimBootstrap\Middleware\Authentication(
            $logger,
            new SlimBootstrap\Acl($aclConfig),
            $authentication
        );
    }

    /**
     * @param array $applicationConfig
     *
     * @return Slim\HttpCache\Cache
     */
    public function getCache(array $applicationConfig): Slim\HttpCache\Cache
    {
        $cacheDuration = 900;

        if (true === \array_key_exists('cacheDuration', $applicationConfig)
            && true === \is_int($applicationConfig['cacheDuration'])
        ) {
            $cacheDuration = $applicationConfig['cacheDuration'];
        }

        return new Slim\HttpCache\Cache('public', $cacheDuration);
    }
}
