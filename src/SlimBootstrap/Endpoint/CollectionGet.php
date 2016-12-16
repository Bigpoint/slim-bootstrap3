<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

/**
 * Interface CollectionGet
 *
 * @package SlimBootstrap\Endpoint
 */
interface CollectionGet extends SlimBootstrap\Endpoint
{
    /**
     * @param array $filters
     *
     * @return array
     */
    public function get(array $filters): array;
}
