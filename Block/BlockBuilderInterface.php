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

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockBuilderInterface extends \Traversable, \Countable, BlockConfigBuilderInterface
{
    /**
     * Adds a new field to this group. A field must have a unique name within
     * the group. Otherwise the existing field is overwritten.
     *
     * If you add a nested group, this group should also be represented in the
     * object hierarchy.
     *
     * @param string|BlockBuilderInterface $child
     * @param string|BlockTypeInterface    $type
     * @param array                        $options
     *
     * @return BlockBuilderInterface The builder object.
     */
    public function add($child, $type = null, array $options = array());

    /**
     * Creates a block builder.
     *
     * @param string                    $name    The name of the block or the name of the property
     * @param string|BlockTypeInterface $type    The type of the block or null if name is a property
     * @param array                     $options The options
     *
     * @return BlockBuilderInterface The created builder.
     */
    public function create($name, $type = null, array $options = array());

    /**
     * Returns a child by name.
     *
     * @param string $name The name of the child
     *
     * @return BlockBuilderInterface The builder for the child
     *
     * @throws Exception\Exception if the given child does not exist
     */
    public function get($name);

    /**
     * Removes the field with the given name.
     *
     * @param string $name
     *
     * @return BlockBuilderInterface The builder object.
     */
    public function remove($name);

    /**
     * Returns whether a field with the given name exists.
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function has($name);

    /**
     * Returns the children.
     *
     * @return array
     */
    public function all();

    /**
     * Returns the associated block factory.
     *
     * @return BlockFactoryInterface The factory
     */
    public function getBlockFactory();

    /**
     * Creates the block.
     *
     * @return Block The block
     */
    public function getBlock();

    /**
     * Sets the parent builder.
     *
     * @param BlockBuilderInterface $parent The parent builder
     *
     * @return BlockBuilderInterface The builder object.
     */
    public function setParent(BlockBuilderInterface $parent = null);

    /**
     * Returns the parent builder.
     *
     * @return BlockBuilderInterface The parent builder
     */
    public function getParent();

    /**
     * Returns whether the builder has a parent.
     *
     * @return Boolean
     */
    public function hasParent();
}
