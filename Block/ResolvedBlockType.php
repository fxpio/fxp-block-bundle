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

use Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException;
use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A wrapper for a block type and its extensions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ResolvedBlockType implements ResolvedBlockTypeInterface
{
    /**
     * @var BlockTypeInterface
     */
    protected $innerType;

    /**
     * @var BlockTypeExtensionInterface[]
     */
    protected $typeExtensions;

    /**
     * @var ResolvedBlockTypeInterface
     */
    protected $parent;

    /**
     * @var OptionsResolver
     */
    protected $optionsResolver;

    /**
     * Constructor.
     *
     * @param BlockTypeInterface            $innerType
     * @param BlockTypeExtensionInterface[] $typeExtensions
     * @param ResolvedBlockTypeInterface    $parent
     *
     * @throws Exception
     */
    public function __construct(BlockTypeInterface $innerType, array $typeExtensions = array(), ResolvedBlockTypeInterface $parent = null)
    {
        if (!preg_match('/^[a-z0-9_]*$/i', $innerType->getName())) {
            throw new InvalidArgumentException(sprintf(
                'The "%s" block type name ("%s") is not valid. Names must only contain letters, numbers, and "_".',
                get_class($innerType),
                $innerType->getName()
            ));
        }

        foreach ($typeExtensions as $extension) {
            if (!$extension instanceof BlockTypeExtensionInterface) {
                throw new UnexpectedTypeException($extension, 'Symfony\Component\Form\FormTypeExtensionInterface');
            }
        }

        $this->innerType = $innerType;
        $this->typeExtensions = $typeExtensions;
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->innerType->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerType()
    {
        return $this->innerType;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions()
    {
        return $this->typeExtensions;
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder(BlockFactoryInterface $factory, $name, array $options = array(), BlockBuilderInterface $parent = null)
    {
        $options = $this->getOptionsResolver()->resolve($options);

        // Should be decoupled from the specific option at some point
        $dataClass = isset($options['data_class']) ? $options['data_class'] : null;

        $builder = new BlockBuilder($name, $dataClass, new EventDispatcher(), $factory, $options);
        $builder->setType($this);
        $builder->setParent($parent);

        $this->buildBlock($builder, $options);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function createView(BlockInterface $block, BlockView $parent = null)
    {
        $options = $block->getConfig()->getOptions();

        $view = new BlockView($parent);

        $this->buildView($view, $block, $options);

        foreach ($block as $name => $child) {
            /* @var BlockInterface $child */
            $view->children[$name] = $child->createView($view);
        }

        $this->finishView($view, $block, $options);

        return $view;
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildBlock($builder, $options);
        }

        $this->innerType->buildBlock($builder, $options);

        foreach ($this->typeExtensions as $extension) {
            /* @var BlockTypeExtensionInterface $extension */
            $extension->buildBlock($builder, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildView($view, $block, $options);
        }

        $this->innerType->buildView($view, $block, $options);

        foreach ($this->typeExtensions as $extension) {
            /* @var BlockTypeExtensionInterface $extension */
            $extension->buildView($view, $block, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->finishView($view, $block, $options);
        }

        $this->innerType->finishView($view, $block, $options);

        foreach ($this->typeExtensions as $extension) {
            /* @var BlockTypeExtensionInterface $extension */
            $extension->finishView($view, $block, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateChild(BlockBuilderInterface $builder, BlockBuilderInterface $builderChild)
    {
        if (null !== $this->parent) {
            $this->parent->validateChild($builder, $builderChild);
        }

        $this->innerType->validateChild($builder, $builderChild);

        foreach ($this->typeExtensions as $extension) {
            /* @var BlockTypeExtensionInterface $extension */
            $extension->validateChild($builder, $builderChild);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsResolver()
    {
        if (null === $this->optionsResolver) {
            if (null !== $this->parent) {
                $this->optionsResolver = clone $this->parent->getOptionsResolver();
            } else {
                $this->optionsResolver = new OptionsResolver();
            }

            $this->innerType->setDefaultOptions($this->optionsResolver);

            foreach ($this->typeExtensions as $extension) {
                /* @var BlockTypeExtensionInterface $extension */
                $extension->setDefaultOptions($this->optionsResolver);
            }
        }

        return $this->optionsResolver;
    }
}
