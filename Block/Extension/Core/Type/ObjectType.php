<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'row'       => true,
            'row_label' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'block';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'object';
    }
}
