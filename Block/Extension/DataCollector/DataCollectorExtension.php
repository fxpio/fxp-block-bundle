<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\DataCollector;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sonatra\Bundle\BlockBundle\Block\AbstractExtension;

/**
 * Extension for collecting data of the blocks on a page.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DataCollectorExtension extends AbstractExtension
{
    /**
     * @var EventSubscriberInterface
     */
    private $dataCollector;

    /**
     * Constructor.
     *
     * @param BlockDataCollectorInterface $dataCollector
     */
    public function __construct(BlockDataCollectorInterface $dataCollector)
    {
        $this->dataCollector = $dataCollector;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadTypeExtensions()
    {
        return array(
            new Type\DataCollectorTypeExtension($this->dataCollector)
        );
    }
}
