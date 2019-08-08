<?php
namespace SlimBootstrap;

use \Psr\Http\Message;
use \SlimBootstrap;

/**
 * Interface Authentication
 *
 * @package SlimBootstrap
 */
interface AuthenticationInterface
{
    /**
     * @param Message\ServerRequestInterface $request The object holding information about the current request.
     *
     * @return array structure {"clientId": "myClient", "role": "myRole"}
     *
     * @throws SlimBootstrap\Exception When the passed access $token is invalid.
     */
    public function authenticate(Message\ServerRequestInterface $request): array;
}
