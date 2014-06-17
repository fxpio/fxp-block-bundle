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
use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Sonatra\Bundle\BlockBundle\Block\Exception\InvalidConfigurationException;
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
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $isDataOptionSet = array_key_exists('data', $options);

        $builder
            ->setAutoInitialize($options['auto_initialize'])
            ->setEmptyData($options['empty_data'])
            ->setMapped($options['mapped'])
            ->setPropertyPath(is_string($options['property_path']) ? $options['property_path'] : null)
            ->setInheritData($options['inherit_data'])
            ->setCompound($options['compound'])
            ->setData($isDataOptionSet ? $options['data'] : null)
            ->setDataMapper($options['compound'] ? new PropertyPathMapper($this->propertyAccessor) : null)
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
        $id = $name;

        if ($view->parent) {
            $uniqueBlockPrefix = sprintf('%s_%s', $view->parent->vars['unique_block_prefix'], $blockName);

            if ($options['chained_block']) {
                $id = sprintf('%s_%s', $view->parent->vars['id'], $name);
            }

            if (!$translationDomain) {
                $translationDomain = $view->parent->vars['translation_domain'];
            }

        } else {
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
                'wrapped'             => $options['wrapped'],
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
            return function (BlockInterface $block) use ($options) {
                $class = $options['data_class'];

                if (null !== $class && null === $block->getConfig()->getData()) {
                    $ref = new \ReflectionClass($class);
                    $constructor = $ref->getConstructor();

                    if (null !== $constructor && !empty($constructor->getParameters())) {
                        throw new InvalidConfigurationException('The option can not create an object with a constructor. Override this option with the creation of a custom object');
                    }

                    return $ref->newInstance();
                }

                return null;
            };
        };

        // If data is given, the block is locked to that data
        // (independent of its value)
        $resolver->setOptional(array(
                'data',
        ));

        $resolver->setDefaults(array(
                'block_name'         => null,
                'id'                 => null,
                'render_id'          => false,
                'row'                => false,
                'row_label'          => false,
                'chained_block'      => false,
                'data_class'         => $dataClass,
                'empty_data'         => $emptyData,
                'property_path'      => null,
                'mapped'             => true,
                'label'              => null,
                'attr'               => array(),
                'label_attr'         => array(),
                'inherit_data'       => false,
                'compound'           => true,
                'wrapped'            => true,
                'translation_domain' => null,
                'auto_initialize'    => true,
        ));

        $resolver->setAllowedTypes(array(
                'attr'            => 'array',
                'label_attr'      => 'array',
                'auto_initialize' => 'bool',
        ));

        $resolver->setNormalizers(array(
            'block_name' => function (Options $options, $value = null) {
                if (isset($options['id'])) {
                    $value = $options['id'];
                }

                return $value;
            },
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
