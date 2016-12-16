<?php
namespace SlimBootstrap\Middleware;

use \Monolog;
use \SlimBootstrap;

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
     * @return SlimBootstrap\Middleware\Header
     */
    public function getHeader(): SlimBootstrap\Middleware\Header
    {
        return new SlimBootstrap\Middleware\Header();
    }

    /**
     * @param array $csvConfig
     *
     * @return SlimBootstrap\Middleware\OutputWriter
     */
    public function getOutputWriter(array $csvConfig): SlimBootstrap\Middleware\OutputWriter
    {
        return new SlimBootstrap\Middleware\OutputWriter($csvConfig);
    }

    /**
     * @param Monolog\Logger                $logger
     * @param \SlimBootstrap\Authentication $authentication
     * @param array                         $aclConfig
     *
     * @return \SlimBootstrap\Middleware\Authentication
     *
     * @throws SlimBootstrap\Exception
     */
    public function getAuthentication(
        Monolog\Logger $logger,
        SlimBootstrap\Authentication $authentication = null,
        array $aclConfig = []
    ): SlimBootstrap\Middleware\Authentication {
        if (false === \is_array($aclConfig)) {
            throw new SlimBootstrap\Exception('acl config is empty or invalid', 500);
        }

        return new SlimBootstrap\Middleware\Authentication(
            $logger,
            new SlimBootstrap\Acl($aclConfig),
            $authentication
        );
    }
}
