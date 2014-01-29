<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractType;
use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\PercentToLocalizedStringTransformer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PercentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();

        $builder->addViewTransformer(
            new PercentToLocalizedStringTransformer(
                $options['precision'],
                $options['grouping'],
                $options['rounding_mode'],
                $options['locale']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'number';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'percent';
    }
}
