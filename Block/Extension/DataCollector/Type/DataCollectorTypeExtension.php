<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\DataCollector\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractTypeExtension;
use Sonatra\Bundle\BlockBundle\Block\Extension\DataCollector\EventListener\DataCollectorListener;
use Sonatra\Bundle\BlockBundle\Block\Extension\DataCollector\BlockDataCollectorInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;

/**
 * Type extension for collecting data of a block with this type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DataCollectorTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventSubscriberInterface
     */
    private $listener;

    /**
     * Constructor.
     *
     * @param BlockDataCollectorInterface $dataCollector
     */
    public function __construct(BlockDataCollectorInterface $dataCollector)
    {
        $this->listener = new DataCollectorListener($dataCollector);
    }

    /**
     * {@inheritDoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->listener);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return 'block';
    }
}
