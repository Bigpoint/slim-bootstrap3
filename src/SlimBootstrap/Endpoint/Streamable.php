<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

/**
 * Interface Streamable
 *
 * @package SlimBootstrap\Endpoint
 */
interface Streamable extends SlimBootstrap\Endpoint
{
    /**
     * @param SlimBootstrap\ResponseOutputWriter\Streamable $outputWriter
     */
    public function setOutputWriter(SlimBootstrap\ResponseOutputWriter\Streamable $outputWriter);
}
