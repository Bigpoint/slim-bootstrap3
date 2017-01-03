<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

/**
 * Interface Get
 *
 * @package SlimBootstrap\Endpoint
 */
interface Get extends SlimBootstrap\Endpoint
{
    /**
     * @param array $routeArguments
     * @param array $data
     *
     * @return array
     */
    public function get(array $routeArguments, array $data): array;
}
