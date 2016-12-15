<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

/**
 * Interface ResourceGet
 *
 * @package SlimBootstrap\Endpoint
 */
interface ResourceGet extends SlimBootstrap\Endpoint
{
    /**
     * @param array $args
     *
     * @return array
     */
    public function get(array $args): array;
}
