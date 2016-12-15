<?php
namespace SlimBootstrap;

use \Psr\Http\Message;
use \SlimBootstrap;

/**
 * Interface Authentication
 *
 * @package SlimBootstrap
 */
interface Authentication
{
    /**
     * @param Message\ServerRequestInterface $request The object holding information about the current request.
     *
     * @return string The clientId of the calling client.
     *
     * @throws SlimBootstrap\Exception When the passed access $token is invalid.
     */
    public function authenticate(Message\ServerRequestInterface $request): string;
}
