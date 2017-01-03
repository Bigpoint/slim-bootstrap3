<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

/**
 * Interface Delete
 *
 * @package SlimBootstrap\Endpoint
 */
interface Delete extends SlimBootstrap\Endpoint
{
    /**
     * @param array $routeArguments
     * @param array $data
     *
     * @return array
     */
    public function delete(array $routeArguments, array $data): array;
}
