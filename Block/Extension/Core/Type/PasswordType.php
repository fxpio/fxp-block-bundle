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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\PasswordTransformer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(
                    new PasswordTransformer(
                            $options['mask'],
                            $options['mask_length'],
                            $options['mask_symbol']
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'mask' => true,
            'mask_length' => 6,
            'mask_symbol' => '*',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'password';
    }
}
