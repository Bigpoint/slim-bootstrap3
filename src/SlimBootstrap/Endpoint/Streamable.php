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
     * @param SlimBootstrap\OutputWriter\Streamable $outputWriter
     */
    public function setOutputWriter(SlimBootstrap\OutputWriter\Streamable $outputWriter);
}
