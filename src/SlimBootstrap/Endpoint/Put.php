<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

/**
 * Interface Put
 *
 * @package SlimBootstrap\Endpoint
 */
interface Put extends SlimBootstrap\Endpoint
{
    /**
     * @param array $routeArguments
     * @param array $queryParameters
     * @param array $data
     *
     * @return array
     */
    public function put(array $routeArguments, array $queryParameters, array $data): array;
}
