<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractType;
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class TwigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        $view->vars = array_replace($view->vars, array(
                'block_rendered' => $options['block_rendered'],
                'rendered'       => $options['rendered'],
                'resource'       => $options['resource'],
                'options'        => array_replace($options['options'],
                        $view->vars,
                        array(
                                'block_rendered' => $options['block_rendered'],
                        )),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'inherit_data'   => true,
                'rendered'       => true,
                'block_rendered' => 'content',
                'resource'       => null,
                'options'        => array(),
        ));

        $resolver->setAllowedTypes(array(
                'block_rendered' => 'string',
                'resource'       => 'string',
                'options'        => 'array',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twig';
    }
}
