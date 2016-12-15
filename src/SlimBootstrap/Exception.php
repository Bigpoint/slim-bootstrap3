<?php
namespace SlimBootstrap;

use \Monolog;

/**
 * This class represents the basic API exception to be thrown if an error occurs.
 *
 * @package SlimBootstrap
 */
class Exception extends \Exception
{
    /**
     * @var int
     */
    private $logLevel = Monolog\Logger::ERROR;

    /**
     * @param string $message
     * @param int    $code
     * @param int    $logLevel
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        int $logLevel = Monolog\Logger::ERROR
    ) {
        parent::__construct($message, $code);

        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getLogLevel(): int
    {
        return $this->logLevel;
    }
}
