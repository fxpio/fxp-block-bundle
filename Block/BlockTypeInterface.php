<?php

/**
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
interface BlockTypeInterface
{
    /**
     * Builds the block.
     *
     * This method is called for each type in the hierarchy starting block the
     * top most type. Type extensions can further modify the block.
     *
     * @param BlockBuilderInterface $builder The block builder
     * @param array                 $options The options
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options);

    /**
     * Finishes the block.
     *
     * This method is called for each type in the hierarchy ending block the
     * top most type. Type extensions can further modify the block.
     *
     * @param BlockBuilderInterface $builder The block builder
     * @param array                 $options The options
     */
    public function finishBlock(BlockBuilderInterface $builder, array $options);

    /**
     * Action when the block is added to parent block.
     *
     * @param BlockInterface $parent  The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function addParent(BlockInterface $parent, BlockInterface $block, array $options);

    /**
     * Action when the block is removed to parent block.
     *
     * @param BlockInterface $parent  The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function removeParent(BlockInterface $parent, BlockInterface $block, array $options);

    /**
     * Action when the block adds a child.
     *
     * @param BlockInterface $child   The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options);

    /**
     * Action when the block removes a child.
     *
     * @param BlockInterface $child   The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function removeChild(BlockInterface $child, BlockInterface $block, array $options);

    /**
     * Builds the block view.
     *
     * This method is called for each type in the hierarchy starting block the
     * top most type. Type extensions can further modify the block view.
     *
     * A block view of a block is built before the blocks of the child blocks are built.
     * This means that you cannot access child blocks views in this method. If you need
     * to do so, move your logic to {@link finishView()} instead.
     *
     * @param BlockView      $view    The block view
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options);

    /**
     * Finishes the block view.
     *
     * This method gets called for each type in the hierarchy starting block the
     * top most type. Type extensions can further modify the block view.
     *
     * When this method is called, blocks of the block's children have already
     * been built and finished and can be accessed. You should only implement
     * such logic in this method that actually accesses child blocks views. For everything
     * else you are recommended to implement {@link buildBlock()} instead.
     *
     * @param BlockView      $view    The block view
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options);

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver);

    /**
     * Returns the name of the parent type.
     *
     * @return string|null The name of the parent type if any, null otherwise.
     */
    public function getParent();

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName();
}
