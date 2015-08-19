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
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class NumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new NumberToLocalizedStringTransformer(
                $options['precision'],
                $options['grouping'],
                $options['rounding_mode'],
                $options['locale']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            // default precision is locale specific (usually around 3)
            'precision'     => null,
            'grouping'      => false,
            'locale'        => \Locale::getDefault(),
            // Integer cast rounds towards 0, so do the same when displaying fractions
            'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_HALF_EVEN,
            'compound'      => false,
        ));

        $resolver->setAllowedValues(array(
            'rounding_mode' => array(
                NumberToLocalizedStringTransformer::ROUND_CEILING,
                NumberToLocalizedStringTransformer::ROUND_DOWN,
                NumberToLocalizedStringTransformer::ROUND_FLOOR,
                NumberToLocalizedStringTransformer::ROUND_HALF_DOWN,
                NumberToLocalizedStringTransformer::ROUND_HALF_EVEN,
                NumberToLocalizedStringTransformer::ROUND_HALF_UP,
                NumberToLocalizedStringTransformer::ROUND_UP,
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'number';
    }
}
