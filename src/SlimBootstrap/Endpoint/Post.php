<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

/**
 * Interface Post
 *
 * @package SlimBootstrap\Endpoint
 */
interface Post extends SlimBootstrap\Endpoint
{
    /**
     * @param array $routeArguments
     * @param array $queryParameters
     * @param array $data
     *
     * @return array
     */
    public function post(array $routeArguments, array $queryParameters, array $data): array;
}
