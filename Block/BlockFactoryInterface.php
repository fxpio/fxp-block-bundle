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
interface BlockFactoryInterface
{
    /**
     * Returns a block.
     *
     * @see createBuilder()
     *
     * @param string|BlockTypeInterface $type    The type of the block
     * @param mixed                     $data    The initial data
     * @param array                     $options The options
     * @param BlockBuilderInterface     $parent  The parent builder
     *
     * @return BlockInterface The block named after the type
     *
     * @throws Exception\UnexpectedTypeException if any given option is not applicable to the given type
     */
    public function create($type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null);

    /**
     * Returns a block.
     *
     * @see createNamedBuilder()
     *
     * @param string                    $name    The name of the block
     * @param string|BlockTypeInterface $type    The type of the block
     * @param mixed                     $data    The initial data
     * @param array                     $options The options
     * @param BlockBuilderInterface     $parent  The parent builder
     *
     * @return BlockInterface The block
     *
     * @throws Exception\UnexpectedTypeException if any given option is not applicable to the given type
     */
    public function createNamed($name, $type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null);

    /**
     * Returns a block for a property of a class.
     *
     * @see createBuilderForProperty()
     *
     * @param string                $class    The fully qualified class name
     * @param string                $property The name of the property to guess for
     * @param mixed                 $data     The initial data
     * @param array                 $options  The options for the builder
     * @param BlockBuilderInterface $parent   The parent builder
     *
     * @return BlockInterface The block named after the property
     *
     * @throws Exception\UnexpectedTypeException if any given option is not applicable to the block type
     */
    public function createForProperty($class, $property, $data = null, array $options = array(), BlockBuilderInterface $parent = null);

    /**
     * Returns a block builder.
     *
     * @param string|BlockTypeInterface $type    The type of the block
     * @param mixed                     $data    The initial data
     * @param array                     $options The options
     * @param BlockBuilderInterface     $parent  The parent builder
     *
     * @return BlockBuilderInterface The block builder
     *
     * @throws Exception\UnexpectedTypeException if any given option is not applicable to the given type
     */
    public function createBuilder($type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null);

    /**
     * Returns a block builder.
     *
     * @param string                    $name    The name of the block
     * @param string|BlockTypeInterface $type    The type of the block
     * @param mixed                     $data    The initial data
     * @param array                     $options The options
     * @param BlockBuilderInterface     $parent  The parent builder
     *
     * @return BlockBuilderInterface The block builder
     *
     * @throws Exception\UnexpectedTypeException if any given option is not applicable to the given type
     */
    public function createNamedBuilder($name, $type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null);

    /**
     * Returns a block builder for a property of a class.
     *
     * If any type options can be guessed, and are not provided in the options
     * argument, the guessed value is used.
     *
     * @param string                $class    The fully qualified class name
     * @param string                $property The name of the property to guess for
     * @param mixed                 $data     The initial data
     * @param array                 $options  The options for the builder
     * @param BlockBuilderInterface $parent   The parent builder
     *
     * @return BlockBuilderInterface The block builder named after the property
     *
     * @throws Exception\UnexpectedTypeException if any given option is not applicable to the block type
     */
    public function createBuilderForProperty($class, $property, $data = null, array $options = array(), BlockBuilderInterface $parent = null);
}
