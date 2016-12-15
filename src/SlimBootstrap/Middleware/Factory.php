<?php
namespace SlimBootstrap\Middleware;

use \Monolog;
use \SlimBootstrap;

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
     * @param SlimBootstrap\ResponseOutputwriter\Factory $responseOutputWriterFactory
     *
     * @return SlimBootstrap\Middleware\ResponseOutputWriter
     */
    public function getResponseOutputWriter(
        SlimBootstrap\ResponseOutputwriter\Factory $responseOutputWriterFactory
    ): SlimBootstrap\Middleware\ResponseOutputWriter {
        return new SlimBootstrap\Middleware\ResponseOutputWriter($responseOutputWriterFactory);
    }

    /**
     * @param Monolog\Logger                     $logger
     * @param \SlimBootstrap\Authentication|null $authentication
     * @param array                              $aclConfig
     *
     * @return \SlimBootstrap\Middleware\Authentication
     */
    public function getAuthentication(
        Monolog\Logger $logger,
        SlimBootstrap\Authentication $authentication = null,
        array $aclConfig = null
    ): SlimBootstrap\Middleware\Authentication {
        return new SlimBootstrap\Middleware\Authentication($logger, $authentication, $aclConfig);
    }
}
