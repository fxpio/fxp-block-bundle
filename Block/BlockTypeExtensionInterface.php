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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockTypeExtensionInterface
{
    /**
     * Builds the block.
     *
     * This method is called after the extended type has built the block to
     * further modify it.
     *
     * @see BlockTypeInterface::buildBlock()
     *
     * @param BlockBuilderInterface $builder The block builder
     * @param array                 $options The options
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options);

    /**
     * Builds the view.
     *
     * This method is called after the extended type has built the view to
     * further modify it.
     *
     * @see BlockTypeInterface::buildView()
     *
     * @param BlockView      $view    The view
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options);

    /**
     * Finishes the view.
     *
     * This method is called after the extended type has finished the view to
     * further modify it.
     *
     * @see BlockTypeInterface::finishView()
     *
     * @param BlockView      $view    The view
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options);

    /**
     * Overrides the default options from the extended type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver);

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType();

    /**
     * Validates the child when is added to this block.
     *
     * @param BlockBuilderInterface $builder
     * @param BlockBuilderInterface $builderChild
     *
     * @throws \Sonatra\Bundle\BlockBundle\Block\Exception\InvalidChildException When the child block is not allowed for this block
     */
    public function validateChild(BlockBuilderInterface $builder, BlockBuilderInterface $builderChild);
}
