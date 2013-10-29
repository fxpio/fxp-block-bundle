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
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataMapper\PropertyPathMapper;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockType extends AbstractType
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * Constructor.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::getPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder
            ->setEmptyData($options['empty_data'])
            ->setMapped($options['mapped'])
            ->setPropertyPath(is_string($options['property_path']) ? $options['property_path'] : null)
            ->setInheritData($options['inherit_data'])
            ->setCompound($options['compound'])
            ->setData(isset($options['data']) ? $options['data'] : null)
            ->setDataMapper($options['mapped'] ? new PropertyPathMapper($this->propertyAccessor) : null)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $name = $block->getName();
        $blockName = $options['block_name'] ?: $block->getName();
        $translationDomain = $options['translation_domain'];

        if ($view->parent && $options['chained_block']) {
            $id = sprintf('%s_%s', $view->parent->vars['id'], $name);
            $uniqueBlockPrefix = sprintf('%s_%s', $view->parent->vars['unique_block_prefix'], $blockName);

            if (!$translationDomain) {
                $translationDomain = $view->parent->vars['translation_domain'];
            }

        } else {
            $id = $name;
            $uniqueBlockPrefix = '_' . $blockName;

            // Strip leading underscores and digits. These are allowed in
            // block names, but not in HTML4 ID attributes.
            // http://www.w3.org/TR/html401/struct/global.html#adef-id
            $id = ltrim($id, '_0123456789');
        }

        $blockPrefixes = array();
        for ($type = $block->getConfig()->getType(); null !== $type; $type = $type->getParent()) {
            array_unshift($blockPrefixes, $type->getName());
        }
        $blockPrefixes[] = $uniqueBlockPrefix;

        if (!$translationDomain) {
            $translationDomain = 'messages';
        }

        $view->vars = array_replace($view->vars, array(
                'block'               => $view,
                'id'                  => $id,
                'name'                => $name,
                'render_id'           => $options['render_id'],
                'row'                 => $options['row'],
                'row_label'           => $options['row_label'],
                'value'               => $block->getViewData(),
                'data'                => $block->getNormData(),
                'label'               => $options['label'],
                'attr'                => $options['attr'],
                'label_attr'          => $options['label_attr'],
                'compound'            => $block->getConfig()->getCompound(),
                'block_prefixes'      => $blockPrefixes,
                'unique_block_prefix' => $uniqueBlockPrefix,
                'translation_domain'  => $translationDomain,
                // Using the block name here speeds up performance in collection
                // blocks, where each entry has the same full block name.
                // Including the type is important too, because if rows of a
                // collection block have different types (dynamically), they should
                // be rendered differently.
                'cache_key'           => $uniqueBlockPrefix . '_' . $block->getConfig()->getType()->getName(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // Derive "data_class" option from passed "data" object
        $dataClass = function (Options $options) {
            return isset($options['data']) && is_object($options['data']) ? get_class($options['data']) : null;
        };

        // Derive "empty_data" closure from "data_class" option
        $emptyData = function (Options $options) {
            $class = $options['data_class'];

            if (null !== $class) {
                return function (BlockInterface $block) use ($class) {
                    return $block->isEmpty() ? null : new $class();
                };
            }

            return function (BlockInterface $block) {
                return $block->getConfig()->getMapped() ? array() : '';
            };
        };

        // former property_path=false now equals mapped=false
        $mapped = function (Options $options) {
            return isset($options['data']) && (is_object($options['data']) || is_array($options['data']));
        };

        // If data is given, the block is locked to that data
        // (independent of its value)
        $resolver->setOptional(array(
                'data',
        ));

        $resolver->setDefaults(array(
                'block_name'         => null,
                'render_id'          => false,
                'row'                => false,
                'row_label'          => false,
                'chained_block'      => false,
                'data_class'         => $dataClass,
                'empty_data'         => $emptyData,
                'property_path'      => null,
                'mapped'             => $mapped,
                'label'              => null,
                'attr'               => array(),
                'label_attr'         => array(),
                'inherit_data'       => false,
                'compound'           => true,
                'translation_domain' => null,
        ));

        $resolver->setAllowedTypes(array(
                'attr'       => 'array',
                'label_attr' => 'array',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'block';
    }
}
