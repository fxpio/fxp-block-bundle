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

use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockFactory implements BlockFactoryInterface
{
    /**
     * @var BlockRegistryInterface
     */
    protected $registry;

    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    protected $resolvedTypeFactory;

    /**
     * Construcotr.
     *
     * @param BlockRegistryInterface            $registry
     * @param ResolvedBlockTypeFactoryInterface $resolvedTypeFactory
     */
    public function __construct(BlockRegistryInterface $registry, ResolvedBlockTypeFactoryInterface $resolvedTypeFactory)
    {
        $this->registry = $registry;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null)
    {
        return $this->createBuilder($type, $data, $options, $parent)->getBlock();
    }

    /**
     * {@inheritdoc}
     */
    public function createNamed($name, $type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null)
    {
        return $this->createNamedBuilder($name, $type, $data, $options, $parent)->getBlock();
    }

    /**
     * {@inheritdoc}
     */
    public function createForProperty($class, $property, $data = null, array $options = array(), BlockBuilderInterface $parent = null)
    {
        return $this->createBuilderForProperty($class, $property, $data, $options, $parent)->getBlock();
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder($type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null)
    {
        $name = $type instanceof BlockTypeInterface || $type instanceof ResolvedBlockTypeInterface
            ? $type->getName()
            : $type;

        return $this->createNamedBuilder($name, $type, $data, $options, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function createNamedBuilder($name, $type = 'block', $data = null, array $options = array(), BlockBuilderInterface $parent = null)
    {
        if (null !== $data && !array_key_exists('data', $options)) {
            $options['data'] = $data;
        }

        if ($type instanceof BlockTypeInterface) {
            $type = $this->resolveType($type);

        } elseif (is_string($type)) {
            $type = $this->registry->getType($type);

        } elseif (!$type instanceof ResolvedBlockTypeInterface) {
            throw new UnexpectedTypeException($type, 'string, Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface or Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface');
        }

        return $type->createBuilder($this, $name, $options, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilderForProperty($class, $property, $data = null, array $options = array(), BlockBuilderInterface $parent = null)
    {
        $guesser = $this->registry->getTypeGuesser();
        $typeGuess = $guesser->guessType($class, $property);

        $type = $typeGuess ? $typeGuess->getType() : 'text';

        // user options may override guessed options
        if ($typeGuess) {
            $options = array_merge($typeGuess->getOptions(), $options);
        }

        return $this->createNamedBuilder($property, $type, $data, $options, $parent);
    }

    /**
     * Wraps a type into a ResolvedBlockTypeInterface implementation and connects
     * it with its parent type.
     *
     * @param BlockTypeInterface $type The type to resolve.
     *
     * @return ResolvedBlockTypeInterface The resolved type.
     */
    private function resolveType(BlockTypeInterface $type)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof BlockTypeInterface) {
            $parentType = $this->resolveType($parentType);

        } elseif (null !== $parentType) {
            $parentType = $this->registry->getType($parentType);
        }

        return $this->resolvedTypeFactory->createResolvedType(
            $type,
            // Type extensions are not supported for unregistered type instances,
            // i.e. type instances that are passed to the BlockFactory directly,
            // nor for their parents, if getParent() also returns a type instance.
            array(),
            $parentType
        );
    }
}
