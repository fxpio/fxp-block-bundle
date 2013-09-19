<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Twig\Block;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Sonatra\Bundle\BlockBundle\Block\BlockView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class TwigRendererEngineTraceable extends TwigRendererEngine implements TwigRendererEngineTraceableInterface
{
    /**
     * @var array
     */
    protected $traces = array();

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * {@inheritdoc}
     */
    public function setStopWatch(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function getTraces()
    {
        return $this->traces;
    }

    /**
     * {@inheritdoc}
     */
    public function renderBlock(BlockView $view, $resource, $blockName, array $variables = array())
    {
        $event = null;

        if ($this->stopwatch instanceof Stopwatch) {
            $event = $this->start($view);
        }

        $output = parent::renderBlock($view, $resource, $blockName, $variables);

        if (null !== $event) {
            $this->end($view, $event);
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function start(BlockView $view)
    {
        $id = $view->vars['id'];
        $name = $view->vars['name'];
        $type = $view->vars['profiler_block_type'];
        $typeClass = $view->vars['profiler_block_type_class'];
        $master = $view->parent === null;

        $this->traces[$id] = array(
                'id'           => $id,
                'name'         => $name,
                'type'         => $type,
                'type_class'   => $typeClass,
                'is_master'    => $master,
                'duration'     => false,
                'memory_used'  => 0,
                'memory_start' => memory_get_usage(),
                'memory_end'   => 0,
                'memory_peak'  => 0,
        );

        $name = sprintf('%s (id: %s, type: %s)', $name, $id, $type);

        return $this->stopwatch->start($name, 'sonatra_block');
    }

    /**
     * {@inheritdoc}
     */
    protected function end(BlockView $view, StopwatchEvent $event)
    {
        $event->stop();

        $id = $view->vars['id'];
        $this->traces[$id] = array_merge($this->traces[$id], array(
                'duration'    => $event->getDuration(),
                'memory_peak' => memory_get_peak_usage(),
                'memory_end'  => memory_get_usage(),
        ));

        $this->traces[$id]['indentation'] = $this->countIndentation($view);
        $this->traces[$id]['memory_used'] = $this->traces[$id]['memory_end'] - $this->traces[$id]['memory_start'];

        if ($this->traces[$id]['memory_used'] < 0) {
            $this->traces[$id]['memory_used'] = 0;
        }
    }

    /**
     * Count indentation.
     *
     * @param BlockView $view
     *
     * @return int
     */
    protected function countIndentation(BlockView $view)
    {
        $count = 0;

        if (null !== $view->parent) {
            $count += 1;
            $count += $this->countIndentation($view->parent);
        }

        return $count;
    }
}
