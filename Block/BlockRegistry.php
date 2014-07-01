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
use Sonatra\Bundle\BlockBundle\Block\Exception\ExceptionInterface;
use Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException;

/**
 * The central registry of the Block component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockRegistry implements BlockRegistryInterface
{
    /**
     * Extensions.
     * @var array An array of BlockExtensionInterface
     */
    protected $extensions = array();

    /**
     * @var array
     */
    protected $types = array();

    /**
     * @var BlockTypeGuesserInterface|false|null
     */
    protected $guesser = false;

    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    protected $resolvedTypeFactory;

    /**
     * Constructor.
     *
     * @param array                             $extensions          An array of BlockExtensionInterface
     * @param ResolvedBlockTypeFactoryInterface $resolvedTypeFactory The factory for resolved block types.
     *
     * @throws UnexpectedTypeException if any extension does not implement BlockExtensionInterface
     */
    public function __construct(array $extensions, ResolvedBlockTypeFactoryInterface $resolvedTypeFactory)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof BlockExtensionInterface) {
                throw new UnexpectedTypeException($extension, 'Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface');
            }
        }

        $this->extensions = $extensions;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!is_string($name)) {
            throw new UnexpectedTypeException($name, 'string');
        }

        if (!isset($this->types[$name])) {
            /** @var BlockTypeInterface $type */
            $type = null;

            foreach ($this->extensions as $extension) {
                /* @var BlockExtensionInterface $extension */
                if ($extension->hasType($name)) {
                    $type = $extension->getType($name);
                    break;
                }
            }

            if (!$type) {
                throw new InvalidArgumentException(sprintf('Could not load type "%s"', $name));
            }

            $this->resolveAndAddType($type);
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (isset($this->types[$name])) {
            return true;
        }

        try {
            $this->getType($name);
        } catch (ExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (false === $this->guesser) {
            $guessers = array();

            foreach ($this->extensions as $extension) {
                /* @var BlockExtensionInterface $extension */
                $guesser = $extension->getTypeGuesser();

                if ($guesser) {
                    $guessers[] = $guesser;
                }
            }

            $this->guesser = !empty($guessers) ? new BlockTypeGuesserChain($guessers) : null;
        }

        return $this->guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Wraps a type into a ResolvedBlockTypeInterface implementation and connects
     * it with its parent type.
     *
     * @param BlockTypeInterface $type The type to resolve.
     *
     * @return ResolvedBlockTypeInterface The resolved type.
     */
    private function resolveAndAddType(BlockTypeInterface $type)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof BlockTypeInterface) {
            $this->resolveAndAddType($parentType);
            $parentType = $parentType->getName();
        }

        $typeExtensions = array();

        foreach ($this->extensions as $extension) {
            /* @var BlockExtensionInterface $extension */
            $typeExtensions = array_merge(
                $typeExtensions,
                $extension->getTypeExtensions($type->getName())
            );
        }

        $rType = $this->resolvedTypeFactory->createResolvedType(
                $type,
                $typeExtensions,
                $parentType ? $this->getType($parentType) : null
        );

        $this->types[$type->getName()] = $rType;
    }
}
