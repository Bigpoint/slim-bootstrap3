<?php
namespace SlimBootstrap;

use \SlimBootstrap;

/**
 * Class Acl
 *
 * @package SlimBootstrap
 */
class Acl
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * Acl constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Checks if the given $clientId is allowed to access the given $endpointName.
     *
     * @param string $clientId     The clientId which wants access to the endpoint
     * @param string $endpointName The endpoint to which the clientId wants access
     *
     * @throws SlimBootstrap\Exception When the clientId was not found in the config, or has no access to the endpoint.
     */
    public function access(string $clientId, string $endpointName)
    {
        if (false === \array_key_exists('access', $this->config)
            || false === \is_array($this->config['access'])
            || false === \array_key_exists($clientId, $this->config['access'])
            || true === empty($this->config['access'][$clientId])
        ) {
            throw new SlimBootstrap\Exception('Access denied', 403);
        }

        $role = $this->config['access'][$clientId];

        if (false === \array_key_exists('roles', $this->config)
            || false === \is_array($this->config['roles'])
            || false === \array_key_exists($role, $this->config['roles'])
            || false === \is_array($this->config['roles'][$role])
            || false === \array_key_exists($endpointName, $this->config['roles'][$role])
            || false === (bool) $this->config['roles'][$role][$endpointName]
        ) {
            throw new SlimBootstrap\Exception('Access denied', 403);
        }
    }
}
