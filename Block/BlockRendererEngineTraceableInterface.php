<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

use Sonatra\Bundle\BlockBundle\Block\BlockRendererEngineInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Adapter for rendering block templates with a profiler access.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockRendererEngineTraceableInterface extends BlockRendererEngineInterface
{
    /**
     * Set stopwatch.
     *
     * @param Stopwatch $stopwatch
     */
    public function setStopWatch(Stopwatch $stopwatch = null);

    /**
     * Get all block traces for profiling.
     *
     * @return array
    */
    public function getTraces();
}
